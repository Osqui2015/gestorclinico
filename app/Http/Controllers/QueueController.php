<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QueueController extends Controller
{
  /**
   * Display the queue board for the current doctor
   */
  public function board()
  {
    $doctor = auth()->user();

    if ($doctor->role !== 'doctor') {
      abort(403, 'Unauthorized');
    }

    // Get today's appointments for this doctor
    $today = now()->startOfDay();
    $todayEnd = now()->endOfDay();

    $appointments = Appointment::query()
      ->where('doctor_id', $doctor->id)
      ->whereBetween('scheduled_at', [$today, $todayEnd])
      ->with('patient')
      ->orderBy('scheduled_at', 'asc')
      ->get();

    // Get current (attending), next (called), and upcoming
    $attending = $appointments->firstWhere('status', 'attending');
    $nextCalled = $appointments->firstWhere('status', 'called');
    $pending = $appointments->where('status', 'pending')->values();
    $called = $appointments->where('status', 'called')->values();

    return Inertia::render('Doctor/Queue', [
      'attending' => $attending,
      'nextCalled' => $nextCalled,
      'pending' => $pending,
      'called' => $called,
      'appointments' => $appointments,
    ]);
  }

  /**
   * Mark appointment as called (called for consultation)
   */
  public function callNext(Appointment $appointment)
  {
    if ($appointment->doctor_id !== auth()->id()) {
      abort(403, 'Unauthorized');
    }

    // If there's an attending appointment, move it to completed
    $attending = Appointment::where('doctor_id', auth()->id())
      ->where('status', 'attending')
      ->first();

    if ($attending) {
      $attending->update(['status' => 'completed']);
    }

    // Update the appointment to attending
    $appointment->update(['status' => 'attending']);

    return back()->with('success', 'Paciente llamado');
  }

  /**
   * Mark appointment as called (next in queue to be called)
   */
  public function markCalled(Appointment $appointment)
  {
    if ($appointment->doctor_id !== auth()->id()) {
      abort(403, 'Unauthorized');
    }

    $appointment->update(['status' => 'called']);

    return back()->with('success', 'Turno marcado como llamado');
  }

  /**
   * Mark appointment as completed
   */
  public function complete(Appointment $appointment)
  {
    if ($appointment->doctor_id !== auth()->id()) {
      abort(403, 'Unauthorized');
    }

    $appointment->update(['status' => 'completed']);

    // Call the next pending appointment
    $nextPending = Appointment::where('doctor_id', auth()->id())
      ->where('status', 'pending')
      ->orderBy('scheduled_at', 'asc')
      ->first();

    if ($nextPending) {
      $nextPending->update(['status' => 'called']);
    }

    return back()->with('success', 'Paciente completado, siguiente paciente llamado');
  }

  /**
   * Get all queues for all doctors (admin view)
   */
  public function allQueues()
  {
    $doctors = User::where('role', 'doctor')
      ->with([
        'appointments' => function ($query) {
          $today = now()->startOfDay();
          $todayEnd = now()->endOfDay();
          $query->whereBetween('scheduled_at', [$today, $todayEnd])
            ->with('patient')
            ->orderBy('scheduled_at', 'asc');
        }
      ])
      ->get();

    $queuesData = $doctors->map(function ($doctor) {
      $appointments = $doctor->appointments;
      return [
        'doctor' => $doctor,
        'attending' => $appointments->firstWhere('status', 'attending'),
        'nextCalled' => $appointments->firstWhere('status', 'called'),
        'pending' => $appointments->where('status', 'pending')->values(),
        'called' => $appointments->where('status', 'called')->values(),
      ];
    });

    return Inertia::render('Admin/Queues', [
      'queues' => $queuesData,
    ]);
  }
}
