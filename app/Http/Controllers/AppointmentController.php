<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Models\DoctorSchedule;
use App\Models\DoctorException;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $appointments = Appointment::query()
            ->with(['doctor', 'patient'])
            ->when(request('status'), fn($query) => $query->where('status', request('status')))
            ->when(request('doctor_id'), fn($query) => $query->where('doctor_id', request('doctor_id')))
            ->when(
                request('search'),
                fn($query) =>
                $query->whereHas(
                    'patient',
                    fn($q) =>
                    $q->where('first_name', 'like', '%' . request('search') . '%')
                        ->orWhere('last_name', 'like', '%' . request('search') . '%')
                )
            )
            ->orderBy('scheduled_at', 'asc')
            ->paginate(10);

        $doctors = User::where('role', 'doctor')->pluck('name', 'id');

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
            'doctors' => $doctors,
            'filters' => request()->only(['status', 'doctor_id', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $patients = Patient::pluck('first_name', 'id')->mapWithKeys(function ($name, $id) {
            $patient = Patient::find($id);
            return [$id => "{$patient->first_name} {$patient->last_name} (DNI: {$patient->dni})"];
        });

        $doctors = User::where('role', 'doctor')->pluck('name', 'id');

        // If user is a doctor, pass their data
        $currentDoctor = null;
        $isDoctor = Auth::user()->role === 'doctor';
        if ($isDoctor) {
            $currentDoctor = Auth::user();
        }

        // If there's a patient_id in query params (from patient profile)
        $preSelectedPatient = request('patient_id');

        // Get patient's insurance if pre-selected
        $preSelectedInsurance = null;
        if ($preSelectedPatient) {
            $patient = Patient::find($preSelectedPatient);
            if ($patient && $patient->primaryInsurance()) {
                $preSelectedInsurance = $patient->primaryInsurance()->id;
            }
        }

        return Inertia::render('Appointments/Create', [
            'patients' => $patients,
            'doctors' => $doctors,
            'currentDoctor' => $currentDoctor,
            'isDoctor' => $isDoctor,
            'preSelectedPatient' => $preSelectedPatient,
            'preSelectedInsurance' => $preSelectedInsurance,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAppointmentRequest $request)
    {
        Appointment::create($request->validated());

        return Redirect::route('appointments.index')->with('success', 'Cita creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['doctor', 'patient']);

        return Inertia::render('Appointments/Show', [
            'appointment' => $appointment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        $appointment->load(['doctor', 'patient', 'healthInsurance']);

        $patients = Patient::pluck('first_name', 'id')->mapWithKeys(function ($name, $id) {
            $patient = Patient::find($id);
            return [$id => "{$patient->first_name} {$patient->last_name}"];
        });

        $doctors = User::where('role', 'doctor')->pluck('name', 'id');

        return Inertia::render('Appointments/Edit', [
            'appointment' => $appointment,
            'patients' => $patients,
            'doctors' => $doctors,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $appointment->update($request->validated());

        return Redirect::route('appointments.index')->with('success', 'Cita actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return Redirect::route('appointments.index')->with('success', 'Cita eliminada exitosamente.');
    }

    /**
     * Get appointments for calendar view
     */
    public function calendar(Request $request)
    {
        $user = $request->user();

        // Get month from request or use current month
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $monthStart = Carbon::parse($month . '-01')->startOfMonth();
        $monthEnd = Carbon::parse($month . '-01')->endOfMonth();

        // Default doctor filter: if authenticated user is a doctor, use their own calendar
        $selectedDoctorId = $request->filled('doctor_id')
            ? (int) $request->input('doctor_id')
            : null;

        if (!$selectedDoctorId && $user && $user->isDoctor()) {
            $selectedDoctorId = $user->id;
        }

        // Build query
        $query = Appointment::with(['doctor', 'patient', 'healthInsurance'])
            ->whereBetween('scheduled_at', [$monthStart, $monthEnd]);

        // Filter by doctor if provided/defaulted
        if ($selectedDoctorId) {
            $query->where('doctor_id', $selectedDoctorId);
        }

        $appointments = $query->orderBy('scheduled_at')->get();

        $doctors = User::where('role', 'doctor')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Appointments/Calendar', [
            'appointments' => $appointments,
            'doctors' => $doctors,
            'currentMonth' => $month,
            'currentDoctorFilter' => $selectedDoctorId,
        ]);
    }

    /**
     * Get available time slots for a doctor on a specific date (API)
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'date' => 'required|date',
        ]);

        return $this->getAvailability($request->doctor_id, $request->date);
    }

    /**
     * Get available time slots for a doctor on a specific date
     * @param int $doctor_id Doctor ID
     * @param string $date Date in format YYYY-MM-DD
     */
    public function getAvailability($doctor_id, $date)
    {
        // Validate that doctor exists
        $doctor = User::where('role', 'doctor')->findOrFail($doctor_id);

        $dateCarbon = Carbon::parse($date);
        $dayOfWeek = strtolower($dateCarbon->format('l')); // monday, tuesday, etc.

        // Check if doctor has an exception for this date
        $exception = DoctorException::where('doctor_id', $doctor_id)
            ->whereDate('exception_date', $date)
            ->first();

        if ($exception) {
            // If it's an all-day exception, no slots available
            if ($exception->is_all_day) {
                return response()->json([
                    'date' => $date,
                    'doctor_id' => $doctor_id,
                    'doctor_name' => $doctor->name,
                    'slots' => [],
                    'message' => 'El médico no está disponible este día: ' . $exception->getTypeLabel(),
                ]);
            }
            // If it's a partial exception, we'll filter those hours later
        }

        // Get doctor's schedule for this day of week
        $schedule = DoctorSchedule::where('doctor_id', $doctor_id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return response()->json([
                'date' => $date,
                'doctor_id' => $doctor_id,
                'doctor_name' => $doctor->name,
                'slots' => [],
                'message' => 'El médico no atiende los ' . ucfirst($dayOfWeek),
            ]);
        }

        // Get existing appointments for this doctor on this date
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        $existingAppointments = Appointment::where('doctor_id', $doctor_id)
            ->whereBetween('scheduled_at', [$startOfDay, $endOfDay])
            ->where('status', '!=', 'cancelled')
            ->pluck('scheduled_at')
            ->map(function ($appointment) {
                return $appointment->format('Y-m-d H:i');
            })
            ->toArray();

        // Generate time slots based on doctor's schedule
        $slots = [];

        // Ensure times are in string format
        $startTimeStr = is_string($schedule->start_time) ? $schedule->start_time : $schedule->start_time->format('H:i:s');
        $endTimeStr = is_string($schedule->end_time) ? $schedule->end_time : $schedule->end_time->format('H:i:s');

        $startTime = Carbon::parse($date . ' ' . $startTimeStr);
        $endTime = Carbon::parse($date . ' ' . $endTimeStr);
        $slotDuration = $schedule->slot_duration ?? 30;

        // Get current time to filter past slots if it's today
        $now = Carbon::now();
        $isToday = Carbon::parse($date)->isToday();

        while ($startTime->lt($endTime)) {
            // Skip past time slots if the selected date is today
            if ($isToday && $startTime->lte($now)) {
                $startTime->addMinutes($slotDuration);
                continue;
            }

            $slotTimeStr = $startTime->format('Y-m-d H:i');
            $isAvailable = !in_array($slotTimeStr, $existingAppointments);

            // Check if slot conflicts with partial exception
            if ($exception && !$exception->is_all_day) {
                $exceptionStartStr = is_string($exception->start_time) ? $exception->start_time : $exception->start_time->format('H:i:s');
                $exceptionEndStr = is_string($exception->end_time) ? $exception->end_time : $exception->end_time->format('H:i:s');

                $exceptionStart = Carbon::parse($date . ' ' . $exceptionStartStr);
                $exceptionEnd = Carbon::parse($date . ' ' . $exceptionEndStr);

                if ($startTime->between($exceptionStart, $exceptionEnd, false)) {
                    $isAvailable = false;
                }
            }

            $slots[] = [
                'time' => $slotTimeStr,
                'available' => $isAvailable,
                'display' => $startTime->format('H:i'),
            ];

            $startTime->addMinutes($slotDuration);
        }

        return response()->json([
            'date' => $date,
            'doctor_id' => $doctor_id,
            'doctor_name' => $doctor->name,
            'slots' => $slots,
            'schedule' => [
                'start' => $schedule->start_time,
                'end' => $schedule->end_time,
                'duration' => $slotDuration,
            ],
        ]);
    }

    /**
     * Get patient's primary insurance and member number (API)
     */
    public function getPatientInsurance(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
        ]);

        $patient = Patient::findOrFail($request->patient_id);
        $primaryInsurance = $patient->primaryInsurance();

        if (!$primaryInsurance) {
            return response()->json([
                'patient_id' => $patient->id,
                'insurance' => null,
                'member_number' => null,
            ]);
        }

        // Get pivot data with member_number
        $pivot = $patient->healthInsurances()
            ->where('health_insurance_id', $primaryInsurance->id)
            ->first()?->pivot;

        return response()->json([
            'patient_id' => $patient->id,
            'insurance' => $primaryInsurance,
            'member_number' => $pivot?->member_number ?? null,
        ]);
    }
}
