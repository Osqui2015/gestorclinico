<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReceptionController extends Controller
{
  /**
   * Display reception dashboard
   */
  public function dashboard(Request $request)
  {
    $today = Carbon::today();
    $selectedDate = $request->date ? Carbon::parse($request->date) : $today;

    // Today's appointments with patients and doctors
    $appointments = Appointment::with([
      'doctor',
      'patient.healthInsurances' => fn($query) => $query
        ->select('health_insurances.id', 'health_insurances.name')
        ->wherePivot('is_primary', true),
    ])
      ->whereDate('scheduled_at', $selectedDate)
      ->orderBy('scheduled_at')
      ->get()
      ->map(function ($appointment) {
        $primaryInsurance = $appointment->patient->healthInsurances->first();

        return [
          'id' => $appointment->id,
          'scheduled_at' => $appointment->scheduled_at,
          'duration' => $appointment->duration,
          'status' => $appointment->status,
          'status_label' => $appointment->status_label,
          'status_color' => $appointment->status_color,
          'confirmed' => $appointment->confirmed,
          'checked_in_at' => $appointment->checked_in_at,
          'is_walk_in' => $appointment->is_walk_in,
          'patient' => [
            'id' => $appointment->patient->id,
            'full_name' => $appointment->patient->full_name,
            'dni' => $appointment->patient->dni,
            'phone' => $appointment->patient->phone,
            'has_allergies' => $appointment->patient->hasAllergies(),
            'allergies' => $appointment->patient->allergies,
            'health_insurance' => $primaryInsurance?->name,
            'member_number' => $primaryInsurance?->pivot?->member_number,
          ],
          'doctor' => [
            'id' => $appointment->doctor->id,
            'name' => $appointment->doctor->name,
            'specialty' => $appointment->doctor->specialty,
          ],
          'reason' => $appointment->reason,
        ];
      });

    // Statistics for today
    $stats = [
      'total' => $appointments->count(),
      'pending' => $appointments->where('status', 'pending')->count(),
      'checked_in' => $appointments->whereNotNull('checked_in_at')->count(),
      'attending' => $appointments->where('status', 'attending')->count(),
      'completed' => $appointments->where('status', 'completed')->count(),
      'cancelled' => $appointments->where('status', 'cancelled')->count(),
      'not_confirmed' => $appointments->where('confirmed', false)->count(),
    ];

    // Doctors working today
    $doctors = User::where('role', 'doctor')
      ->whereHas('appointments', function ($q) use ($selectedDate) {
        $q->whereDate('scheduled_at', $selectedDate);
      })
      ->withCount([
        'appointments as today_appointments' => function ($q) use ($selectedDate) {
          $q->whereDate('scheduled_at', $selectedDate);
        },
        'appointments as pending_appointments' => function ($q) use ($selectedDate) {
          $q->whereDate('scheduled_at', $selectedDate)
            ->where('status', 'pending');
        }
      ])
      ->get();

    return Inertia::render('Reception/Dashboard', [
      'appointments' => $appointments,
      'stats' => $stats,
      'doctors' => $doctors,
      'selectedDate' => $selectedDate->format('Y-m-d'),
      'isToday' => $selectedDate->isToday(),
    ]);
  }

  /**
   * Check in a patient
   */
  public function checkIn(Appointment $appointment)
  {
    if ($appointment->checked_in_at) {
      return back()->withErrors(['error' => 'El paciente ya realizó check-in']);
    }

    $appointment->checkIn();

    return back()->with('success', 'Check-in realizado exitosamente');
  }

  /**
   * Confirm appointment
   */
  public function confirm(Appointment $appointment)
  {
    if ($appointment->confirmed) {
      return back()->withErrors(['error' => 'El turno ya está confirmado']);
    }

    $appointment->markAsConfirmed();

    return back()->with('success', 'Turno confirmado exitosamente');
  }

  /**
   * Quick patient search
   */
  public function searchPatient(Request $request)
  {
    $search = $request->input('q');

    if (empty($search)) {
      return response()->json([]);
    }

    $patients = Patient::with([
      'healthInsurances' => fn($query) => $query
        ->select('health_insurances.id', 'health_insurances.name')
        ->wherePivot('is_primary', true),
    ])->where(function ($query) use ($search) {
      $query->where('dni', 'like', "%{$search}%")
        ->orWhere('first_name', 'like', "%{$search}%")
        ->orWhere('last_name', 'like', "%{$search}%")
        ->orWhere('phone', 'like', "%{$search}%");
    })
      ->limit(10)
      ->get()
      ->map(function ($patient) {
        $primaryInsurance = $patient->healthInsurances->first();

        return [
          'id' => $patient->id,
          'full_name' => $patient->full_name,
          'dni' => $patient->dni,
          'phone' => $patient->phone,
          'email' => $patient->email,
          'age' => $patient->age,
          'has_allergies' => $patient->hasAllergies(),
          'health_insurance' => $primaryInsurance?->name,
          'member_number' => $primaryInsurance?->pivot?->member_number,
        ];
      });

    return response()->json($patients);
  }

  /**
   * Quick patient registration
   */
  public function quickRegister(Request $request)
  {
    $validated = $request->validate([
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'dni' => 'required|string|unique:patients,dni|regex:/^\d{7,}$/',
      'birth_date' => 'required|date|before:today',
      'phone' => 'required|string|max:20',
      'email' => 'nullable|email|max:255',
      'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
    ]);

    $patient = Patient::create($validated);

    return response()->json([
      'success' => true,
      'patient' => [
        'id' => $patient->id,
        'full_name' => $patient->full_name,
        'dni' => $patient->dni,
        'phone' => $patient->phone,
      ],
      'message' => 'Paciente registrado exitosamente',
    ]);
  }

  /**
   * Display waiting room
   */
  public function waitingRoom(Request $request)
  {
    $appointments = Appointment::with(['patient', 'doctor'])
      ->whereDate('scheduled_at', Carbon::today())
      ->whereNotNull('checked_in_at')
      ->whereIn('status', ['pending', 'called'])
      ->orderBy('checked_in_at')
      ->get();

    return Inertia::render('Reception/WaitingRoom', [
      'appointments' => $appointments,
    ]);
  }

  /**
   * Display appointments by doctor
   */
  public function byDoctor(Request $request, User $doctor)
  {
    if (!$doctor->isDoctor()) {
      abort(404);
    }

    $date = $request->date ? Carbon::parse($request->date) : Carbon::today();

    $appointments = Appointment::with(['patient'])
      ->where('doctor_id', $doctor->id)
      ->whereDate('scheduled_at', $date)
      ->orderBy('scheduled_at')
      ->get();

    return Inertia::render('Reception/DoctorAppointments', [
      'doctor' => $doctor,
      'appointments' => $appointments,
      'selectedDate' => $date->format('Y-m-d'),
    ]);
  }
}
