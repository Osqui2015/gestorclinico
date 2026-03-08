<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $user = $request->user();

    // Redirect pharmacy users to pharmacy dashboard
    if ($user->role === 'pharmacy') {
      return redirect()->route('pharmacy.dashboard');
    }

    // Get appointments for the authenticated user
    // If doctor, get their appointments; if admin, get all
    if ($user->isAdmin()) {
      $appointments = Appointment::with(['doctor', 'patient'])
        ->where('scheduled_at', '>=', now()->subDays(30))
        ->orderBy('scheduled_at')
        ->get();
    } else {
      // Doctor - get their appointments
      $appointments = Appointment::with(['patient'])
        ->where('doctor_id', $user->id)
        ->where('scheduled_at', '>=', now()->subDays(30))
        ->orderBy('scheduled_at')
        ->get();
    }

    return Inertia::render('Dashboard', [
      'appointments' => $appointments,
      'todayDate' => now()->toDateString(),
    ]);
  }
}
