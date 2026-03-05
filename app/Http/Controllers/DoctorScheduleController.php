<?php

namespace App\Http\Controllers;

use App\Models\DoctorSchedule;
use App\Models\DoctorException;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DoctorScheduleController extends Controller
{
  /**
   * Display doctor schedules
   */
  public function index(Request $request)
  {
    $doctorId = $request->user()->isAdmin()
      ? $request->input('doctor_id')
      : $request->user()->id;

    $query = DoctorSchedule::with('doctor')
      ->when($doctorId, fn($q) => $q->where('doctor_id', $doctorId))
      ->orderBy('day_of_week')
      ->orderBy('start_time');

    $schedules = $query->get();

    // Add day_name to each schedule
    $schedules->transform(function ($schedule) {
      $schedule->day_name = $schedule->getDayNameAttribute();
      return $schedule;
    });

    $doctors = User::where('role', 'doctor')
      ->orderBy('name')
      ->get(['id', 'name', 'specialty']);

    return Inertia::render('DoctorSchedules/Index', [
      'schedules' => $schedules,
      'doctors' => $doctors,
      'selectedDoctorId' => $doctorId,
    ]);
  }

  /**
   * Show form to create schedule
   */
  public function create(Request $request)
  {
    $doctors = User::where('role', 'doctor')
      ->orderBy('name')
      ->get(['id', 'name', 'specialty']);

    $doctorId = $request->user()->isDoctor()
      ? $request->user()->id
      : null;

    return Inertia::render('DoctorSchedules/Create', [
      'doctors' => $doctors,
      'defaultDoctorId' => $doctorId,
    ]);
  }

  /**
   * Store new schedule (supports multiple days)
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'doctor_id' => 'required|exists:users,id',
      'days_of_week' => 'required|array|min:1',
      'days_of_week.*' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
      'start_time' => 'required|date_format:H:i',
      'end_time' => 'required|date_format:H:i|after:start_time',
      'slot_duration' => 'required|integer|min:10|max:120',
    ]);

    $daysCreated = 0;
    $daysSkipped = 0;

    // Create a schedule for each selected day
    foreach ($validated['days_of_week'] as $day) {
      // Check if schedule already exists for this doctor and day
      $exists = DoctorSchedule::where('doctor_id', $validated['doctor_id'])
        ->where('day_of_week', $day)
        ->exists();

      if (!$exists) {
        DoctorSchedule::create([
          'doctor_id' => $validated['doctor_id'],
          'day_of_week' => $day,
          'start_time' => $validated['start_time'],
          'end_time' => $validated['end_time'],
          'slot_duration' => $validated['slot_duration'],
        ]);
        $daysCreated++;
      } else {
        $daysSkipped++;
      }
    }

    $message = "Horarios creados exitosamente para {$daysCreated} día(s)";
    if ($daysSkipped > 0) {
      $message .= ". {$daysSkipped} día(s) ya tenían horario configurado.";
    }

    return redirect()->route('doctor-schedules.index')
      ->with('success', $message);
  }

  /**
   * Show form to edit schedule
   */
  public function edit(DoctorSchedule $doctorSchedule)
  {
    $doctors = User::where('role', 'doctor')
      ->orderBy('name')
      ->get(['id', 'name', 'specialty']);

    return Inertia::render('DoctorSchedules/Edit', [
      'schedule' => $doctorSchedule->load('doctor'),
      'doctors' => $doctors,
    ]);
  }

  /**
   * Update schedule
   */
  public function update(Request $request, DoctorSchedule $doctorSchedule)
  {
    $validated = $request->validate([
      'doctor_id' => 'required|exists:users,id',
      'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
      'start_time' => 'required|date_format:H:i',
      'end_time' => 'required|date_format:H:i|after:start_time',
      'slot_duration' => 'required|integer|min:10|max:120',
      'is_active' => 'boolean',
    ]);

    $doctorSchedule->update($validated);

    return redirect()->route('doctor-schedules.index')
      ->with('success', 'Horario actualizado exitosamente');
  }

  /**
   * Delete schedule
   */
  public function destroy(DoctorSchedule $doctorSchedule)
  {
    $doctorSchedule->delete();

    return redirect()->route('doctor-schedules.index')
      ->with('success', 'Horario eliminado exitosamente');
  }

  /**
   * Display doctor exceptions
   */
  public function indexExceptions(Request $request)
  {
    $doctorId = $request->user()->isAdmin()
      ? $request->input('doctor_id')
      : $request->user()->id;

    $query = DoctorException::with('doctor')
      ->when($doctorId, fn($q) => $q->where('doctor_id', $doctorId))
      ->orderBy('exception_date', 'desc');

    $exceptions = $query->paginate(20);

    // Add type_label to each exception
    $exceptions->getCollection()->transform(function ($exception) {
      $exception->type_label = $exception->getTypeLabel();
      return $exception;
    });

    $doctors = User::where('role', 'doctor')
      ->orderBy('name')
      ->get(['id', 'name', 'specialty']);

    return Inertia::render('DoctorSchedules/Exceptions', [
      'exceptions' => $exceptions,
      'doctors' => $doctors,
      'selectedDoctorId' => $doctorId,
    ]);
  }

  /**
   * Store new exception
   */
  public function storeException(Request $request)
  {
    $validated = $request->validate([
      'doctor_id' => 'required|exists:users,id',
      'exception_date' => 'required|date',
      'type' => 'required|in:vacation,sick_leave,holiday,conference,other',
      'reason' => 'nullable|string|max:255',
      'is_all_day' => 'boolean',
      'start_time' => 'nullable|required_if:is_all_day,false|date_format:H:i',
      'end_time' => 'nullable|required_if:is_all_day,false|date_format:H:i|after:start_time',
    ]);

    DoctorException::create($validated);

    return back()->with('success', 'Excepción creada exitosamente');
  }

  /**
   * Delete exception
   */
  public function destroyException(DoctorException $exception)
  {
    $exception->delete();

    return back()->with('success', 'Excepción eliminada exitosamente');
  }
}
