<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
  /**
   * Doctor's dashboard - Statistics for appointments and patients
   */
  public function doctorDashboard()
  {
    $doctor = Auth::user();

    // Default: current month statistics
    $period = request('period', 'month');
    $startDate = $this->getStartDate($period);
    $endDate = Carbon::now();

    // Get appointments for the doctor in the period
    $appointments = Appointment::where('doctor_id', $doctor->id)
      ->whereBetween('scheduled_at', [$startDate, $endDate])
      ->with(['patient', 'healthInsurance'])
      ->get();

    // Statistics
    $totalAppointments = $appointments->count();
    $completedAppointments = $appointments->where('status', 'completed')->count();
    $cancelledAppointments = $appointments->where('status', 'cancelled')->count();
    $pendingAppointments = $appointments->where('status', 'pending')->count();
    $patientsAttended = $appointments
      ->where('status', 'completed')
      ->pluck('patient_id')
      ->filter()
      ->unique()
      ->count();

    // Coseguro statistics (only completed appointments)
    $completedWithCoseguro = $appointments->where('status', 'completed');
    $totalCoseguro = $completedWithCoseguro->sum('coseguro');
    $coseguroByInsurance = $this->getCoseguroByInsurance($completedWithCoseguro);

    // Patient ages distribution
    $ageDistribution = $this->getAgeDistribution($appointments->pluck('patient'));

    // Health insurance distribution (sobre pacientes atendidos)
    $completedPatients = $appointments
      ->where('status', 'completed')
      ->pluck('patient');
    $insuranceDistribution = $this->getInsuranceDistribution($completedPatients);
    $topInsurance = $this->getTopInsurance($insuranceDistribution);

    // Appointments by day of week
    $appointmentsByDayOfWeek = $this->getAppointmentsByDayOfWeek($appointments);

    // Top patients (most appointments)
    $topPatients = $appointments
      ->groupBy('patient_id')
      ->map(function ($group) {
        return [
          'patient' => $group->first()->patient,
          'count' => $group->count(),
        ];
      })
      ->sortByDesc('count')
      ->take(5)
      ->values();

    return Inertia::render('Reports/DoctorDashboard', [
      'doctor' => $doctor,
      'period' => $period,
      'statistics' => [
        'totalAppointments' => $totalAppointments,
        'completedAppointments' => $completedAppointments,
        'cancelledAppointments' => $cancelledAppointments,
        'pendingAppointments' => $pendingAppointments,
        'patientsAttended' => $patientsAttended,
        'totalCoseguro' => $totalCoseguro,
      ],
      'topInsurance' => $topInsurance,
      'coseguroByInsurance' => $coseguroByInsurance,
      'ageDistribution' => $ageDistribution,
      'insuranceDistribution' => $insuranceDistribution,
      'appointmentsByDayOfWeek' => $appointmentsByDayOfWeek,
      'topPatients' => $topPatients,
    ]);
  }

  /**
   * Admin dashboard - Statistics for all doctors and clinics
   */
  public function adminDashboard()
  {
    $period = request('period', 'month');
    $startDate = $this->getStartDate($period);
    $endDate = Carbon::now();

    // Get all appointments in the period
    $appointments = Appointment::whereBetween('scheduled_at', [$startDate, $endDate])
      ->with('doctor', 'patient')
      ->get();

    // Total statistics
    $totalAppointments = $appointments->count();
    $totalPatients = $appointments->groupBy('patient_id')->count();
    $totalDoctors = User::where('role', 'doctor')->count();

    // Appointments by doctor
    $appointmentsByDoctor = $appointments
      ->groupBy('doctor_id')
      ->map(function ($doctorAppointments) {
        $doctor = $doctorAppointments->first()->doctor;
        return [
          'doctor' => $doctor,
          'totalAppointments' => $doctorAppointments->count(),
          'completed' => $doctorAppointments->where('status', 'completed')->count(),
          'cancelled' => $doctorAppointments->where('status', 'cancelled')->count(),
          'pending' => $doctorAppointments->where('status', 'pending')->count(),
        ];
      })
      ->sortByDesc('totalAppointments')
      ->values();

    // Appointments by specialty
    $appointmentsBySpecialty = $appointments
      ->groupBy(function ($appointment) {
        return $appointment->doctor->specialty;
      })
      ->map(function ($specialtyAppointments) {
        return [
          'specialty' => $specialtyAppointments->first()->doctor->specialty,
          'totalAppointments' => $specialtyAppointments->count(),
          'uniquePatients' => $specialtyAppointments->groupBy('patient_id')->count(),
        ];
      })
      ->sortByDesc('totalAppointments')
      ->values();

    // Patient age distribution
    $ageDistribution = $this->getAgeDistribution($appointments->pluck('patient'));

    // Health insurance distribution
    $insuranceDistribution = $this->getInsuranceDistribution($appointments->pluck('patient'));

    // Appointments trend by day
    $appointmentsTrend = $this->getAppointmentsTrend($appointments, $period);

    // Doctor performance
    $doctorPerformance = User::where('role', 'doctor')
      ->select('id', 'name', 'specialty')
      ->get()
      ->map(function ($doctor) use ($appointmentsByDoctor) {
        $stats = $appointmentsByDoctor->firstWhere('doctor.id', $doctor->id);
        return [
          'id' => $doctor->id,
          'name' => $doctor->name,
          'specialty' => $doctor->specialty,
          'totalAppointments' => $stats['totalAppointments'] ?? 0,
          'completedRate' => $stats['totalAppointments'] > 0
            ? round(($stats['completed'] / $stats['totalAppointments']) * 100, 2)
            : 0,
        ];
      })
      ->sortByDesc('totalAppointments')
      ->values();

    return Inertia::render('Reports/AdminDashboard', [
      'period' => $period,
      'statistics' => [
        'totalAppointments' => $totalAppointments,
        'totalPatients' => $totalPatients,
        'totalDoctors' => $totalDoctors,
        'averageAppointmentsPerDoctor' => $totalDoctors > 0
          ? round($totalAppointments / $totalDoctors, 2)
          : 0,
      ],
      'appointmentsByDoctor' => $appointmentsByDoctor,
      'appointmentsBySpecialty' => $appointmentsBySpecialty,
      'ageDistribution' => $ageDistribution,
      'insuranceDistribution' => $insuranceDistribution,
      'appointmentsTrend' => $appointmentsTrend,
      'doctorPerformance' => $doctorPerformance,
    ]);
  }

  /**
   * Get start date based on period
   */
  private function getStartDate($period): Carbon
  {
    return match ($period) {
      'week' => Carbon::now()->startOfWeek(),
      'month' => Carbon::now()->startOfMonth(),
      'quarter' => Carbon::now()->startOfQuarter(),
      'year' => Carbon::now()->startOfYear(),
      default => Carbon::now()->startOfMonth(),
    };
  }

  /**
   * Get age distribution of patients
   */
  private function getAgeDistribution($patients)
  {
    $ranges = [
      '0-18' => 0,
      '19-30' => 0,
      '31-45' => 0,
      '46-60' => 0,
      '60+' => 0,
    ];

    foreach ($patients as $patient) {
      if ($patient && $patient->birth_date) {
        $age = $patient->birth_date->age;
        if ($age <= 18) {
          $ranges['0-18']++;
        } elseif ($age <= 30) {
          $ranges['19-30']++;
        } elseif ($age <= 45) {
          $ranges['31-45']++;
        } elseif ($age <= 60) {
          $ranges['46-60']++;
        } else {
          $ranges['60+']++;
        }
      }
    }

    return $ranges;
  }

  /**
   * Get health insurance distribution
   */
  private function getInsuranceDistribution($patients)
  {
    $distribution = [];

    foreach ($patients->unique('id') as $patient) {
      if ($patient && $patient->primaryInsurance()) {
        $insuranceName = $patient->primaryInsurance()->name ?? 'Sin Cobertura';
        $distribution[$insuranceName] = ($distribution[$insuranceName] ?? 0) + 1;
      } else {
        $distribution['Sin Cobertura'] = ($distribution['Sin Cobertura'] ?? 0) + 1;
      }
    }

    // Sort by count descending
    arsort($distribution);

    return $distribution;
  }

  /**
   * Get most attended health insurance
   */
  private function getTopInsurance(array $distribution): array
  {
    if (empty($distribution)) {
      return [
        'name' => 'Sin datos',
        'count' => 0,
      ];
    }

    $name = array_key_first($distribution);

    return [
      'name' => $name,
      'count' => $distribution[$name] ?? 0,
    ];
  }

  /**
   * Get appointments by day of week
   */
  private function getAppointmentsByDayOfWeek($appointments)
  {
    $days = [
      'Lunes' => 0,
      'Martes' => 0,
      'Miércoles' => 0,
      'Jueves' => 0,
      'Viernes' => 0,
      'Sábado' => 0,
      'Domingo' => 0,
    ];

    $dayMap = [
      'Monday' => 'Lunes',
      'Tuesday' => 'Martes',
      'Wednesday' => 'Miércoles',
      'Thursday' => 'Jueves',
      'Friday' => 'Viernes',
      'Saturday' => 'Sábado',
      'Sunday' => 'Domingo',
    ];

    foreach ($appointments as $appointment) {
      $dayName = $appointment->scheduled_at->format('l');
      $spanishDay = $dayMap[$dayName];
      $days[$spanishDay]++;
    }

    return $days;
  }

  /**
   * Get appointments trend
   */
  private function getAppointmentsTrend($appointments, $period)
  {
    $trend = [];

    // Generate dates based on period
    $start = $this->getStartDate($period);
    $end = Carbon::now();

    if ($period === 'week') {
      // Daily for the week
      for ($i = 0; $i <= 6; $i++) {
        $date = $start->clone()->addDays($i);
        $dateStr = $date->format('d/m');
        $count = $appointments->filter(function ($appointment) use ($date) {
          return $appointment->scheduled_at->format('Y-m-d') === $date->format('Y-m-d');
        })->count();
        $trend[$dateStr] = $count;
      }
    } elseif ($period === 'month') {
      // Daily for the month
      $date = $start->clone();
      while ($date <= $end) {
        $dateStr = $date->format('d/m');
        $count = $appointments->filter(function ($appointment) use ($date) {
          return $appointment->scheduled_at->format('Y-m-d') === $date->format('Y-m-d');
        })->count();
        $trend[$dateStr] = $count;
        $date->addDay();
      }
    } elseif ($period === 'quarter' || $period === 'year') {
      // Monthly
      $date = $start->clone();
      while ($date <= $end) {
        $dateStr = $date->format('M');
        if (!isset($trend[$dateStr])) {
          $trend[$dateStr] = 0;
        }
        $count = $appointments->filter(function ($appointment) use ($date) {
          return $appointment->scheduled_at->format('Y-m') === $date->format('Y-m');
        })->count();
        $trend[$dateStr] += $count;
        $date->addMonth();
      }
    }

    return $trend;
  }

  /**
   * Get coseguro totals by insurance
   */
  private function getCoseguroByInsurance($appointments)
  {
    $coseguroByInsurance = [];

    foreach ($appointments as $appointment) {
      if ($appointment->coseguro && $appointment->coseguro > 0) {
        $insuranceName = $appointment->healthInsurance
          ? $appointment->healthInsurance->name
          : 'Sin Cobertura';

        if (!isset($coseguroByInsurance[$insuranceName])) {
          $coseguroByInsurance[$insuranceName] = 0;
        }

        $coseguroByInsurance[$insuranceName] += $appointment->coseguro;
      }
    }

    // Sort by amount descending
    arsort($coseguroByInsurance);

    return $coseguroByInsurance;
  }
}
