<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\EmergencyAdmission;
use App\Models\HealthInsurance;
use App\Models\Hospitalization;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdvancedReportController extends Controller
{
  /**
   * Dashboard principal de reportes.
   */
  public function index()
  {
    return Inertia::render('Reports/Dashboard', [
      'stats' => [
        'total_patients_attended' => Appointment::where('status', 'completed')->distinct('patient_id')->count('patient_id'),
        'total_revenue' => (float) Invoice::where('status', 'paid')->sum('total'),
        'pending_payment' => (float) Invoice::whereIn('status', ['pending', 'partially_paid'])->sum('total'),
        'average_appointment_time' => $this->getAverageAppointmentTime(),
        'hospitalization_rate' => $this->getHospitalizationRate(),
      ],
      'monthly_stats' => $this->getMonthlyStats(),
    ]);
  }

  /**
   * Reporte C2 (REFES).
   */
  public function c2Report()
  {
    $consultations = $this->getConsultationsBySpecialtyAndDoctor();
    $totalConsultations = (int) collect($consultations)->sum('count');
    $totalReferrals = (int) collect($consultations)->sum('referrals');

    return Inertia::render('Reports/C2Report', [
      'consultations' => $consultations,
      'total_consultations' => $totalConsultations,
      'total_referrals' => $totalReferrals,
      'referral_rate' => $totalConsultations > 0 ? $totalReferrals / $totalConsultations : 0,
    ]);
  }

  /**
   * Reporte epidemiologico.
   */
  public function epidemiologyReport()
  {
    return Inertia::render('Reports/Epidemiology', [
      'notifiable_diseases' => $this->getNotifiableDiseases(),
      'top_diagnoses' => $this->getTopDiagnoses(),
      'age_distribution' => $this->getAgeDistribution(),
      'gender_distribution' => $this->getGenderDistribution(),
    ]);
  }

  /**
   * Indicadores de calidad.
   */
  public function qualityIndicators()
  {
    return Inertia::render('Reports/QualityIndicators', [
      'metrics' => [
        'average_wait_time' => $this->getAverageWaitTime(),
        'patient_satisfaction' => $this->getPatientSatisfaction(),
        'employee_productivity' => $this->getEmployeeProductivity(),
        'appointment_utilization' => $this->getAppointmentUtilization(),
        'revenue_per_doctor' => $this->getRevenuePerDoctor(),
        'emergency_response_time' => $this->getEmergencyResponseTime(),
        'hospitalization_metrics' => [
          'average_length_stay' => $this->getAverageLengthOfStay(),
          'occupancy_rate' => $this->getCurrentBedOccupancyRatio(),
        ],
      ],
    ]);
  }

  /**
   * Reporte por obra social.
   */
  public function insuranceReport()
  {
    $insurances = HealthInsurance::query()
      ->withCount(['invoices as total_billings'])
      ->withSum('invoices as total_revenue', 'total')
      ->orderBy('name')
      ->get()
      ->map(function (HealthInsurance $insurance) {
        $totalRevenue = (float) ($insurance->total_revenue ?? 0);
        $collected = (float) Invoice::where('health_insurance_id', $insurance->id)
          ->where('status', 'paid')
          ->sum('total');

        return [
          'insurance_name' => $insurance->name,
          'total_billings' => (int) ($insurance->total_billings ?? 0),
          'total_consultations' => Appointment::where('health_insurance_id', $insurance->id)
            ->where('status', 'completed')
            ->count(),
          'total_revenue' => $totalRevenue,
          'collection_rate' => $totalRevenue > 0 ? $collected / $totalRevenue : 0,
        ];
      })
      ->values();

    return Inertia::render('Reports/InsuranceReport', [
      'insurances' => $insurances,
      'total_billings' => (int) $insurances->sum('total_billings'),
      'total_consultations' => (int) $insurances->sum('total_consultations'),
      'total_revenue' => (float) $insurances->sum('total_revenue'),
    ]);
  }

  /**
   * Analisis de facturacion.
   */
  public function billingAnalysis()
  {
    $totalBilled = (float) Invoice::sum('total');
    $totalCollected = (float) Invoice::where('status', 'paid')->sum('total');
    $pendingCollection = (float) Invoice::whereIn('status', ['pending', 'partially_paid'])->sum('total');

    return Inertia::render('Reports/BillingAnalysis', [
      'total_billed' => $totalBilled,
      'total_collected' => $totalCollected,
      'pending_collection' => $pendingCollection,
      'collection_rate' => $totalBilled > 0 ? $totalCollected / $totalBilled : 0,
      'billing_trend' => $this->getBillingTrend(),
      'aging_analysis' => $this->getAgingAnalysis(),
    ]);
  }

  /**
   * Ocupancia de camas.
   */
  public function bedOccupancyReport()
  {
    $totalBeds = Bed::active()->count();
    $occupiedBeds = Bed::where('status', Bed::STATUS_OCCUPIED)->count();
    $availableBeds = Bed::where('status', Bed::STATUS_AVAILABLE)->count();
    $pendingCleaning = Bed::where('status', Bed::STATUS_PENDING_CLEANING)->count();

    $roomOccupancy = Room::active()
      ->withCount([
        'activeBeds as total_beds',
        'occupiedBeds as occupied_beds',
      ])
      ->orderBy('name')
      ->get()
      ->map(function (Room $room) {
        $total = (int) ($room->total_beds ?? 0);
        $occupied = (int) ($room->occupied_beds ?? 0);

        return [
          'room_name' => $room->name,
          'total_beds' => $total,
          'occupied_beds' => $occupied,
          'occupancy_rate' => $total > 0 ? $occupied / $total : 0,
        ];
      })
      ->values();

    return Inertia::render('Reports/BedOccupancy', [
      'total_beds' => $totalBeds,
      'occupied_beds' => $occupiedBeds,
      'available_beds' => $availableBeds,
      'occupancy_rate' => $totalBeds > 0 ? $occupiedBeds / $totalBeds : 0,
      'average_length_stay' => $this->getAverageLengthOfStay(),
      'turnover_rate' => $this->getBedTurnoverRate(),
      'beds_pending_cleaning' => $pendingCleaning,
      'room_occupancy' => $roomOccupancy,
    ]);
  }

  private function getAverageAppointmentTime(): float
  {
    return round((float) (Appointment::where('status', 'completed')->avg('duration') ?? 0), 2);
  }

  private function getHospitalizationRate(): float
  {
    $completedAppointments = Appointment::where('status', 'completed')->count();
    if ($completedAppointments === 0) {
      return 0;
    }

    return Hospitalization::count() / $completedAppointments;
  }

  private function getMonthlyStats(): array
  {
    $months = [];

    for ($i = 11; $i >= 0; $i--) {
      $date = now()->subMonths($i);
      $start = $date->copy()->startOfMonth();
      $end = $date->copy()->endOfMonth();

      $appointments = Appointment::whereBetween('scheduled_at', [$start, $end])
        ->where('status', 'completed');

      $months[] = [
        'month' => $date->format('m/Y'),
        'patients' => (clone $appointments)->distinct('patient_id')->count('patient_id'),
        'appointments' => (clone $appointments)->count(),
        'revenue' => (float) Invoice::whereBetween('created_at', [$start, $end])->sum('total'),
      ];
    }

    return $months;
  }

  private function getConsultationsBySpecialtyAndDoctor(): array
  {
    return User::where('role', 'doctor')
      ->orderBy('name')
      ->get()
      ->map(function (User $doctor) {
        $count = Appointment::where('doctor_id', $doctor->id)
          ->where('status', 'completed')
          ->count();

        return [
          'specialty' => $doctor->specialty ?: 'General',
          'doctor' => $doctor->name,
          'count' => $count,
          'referrals' => 0,
        ];
      })
      ->values()
      ->all();
  }

  private function getNotifiableDiseases(): array
  {
    $notifiable = [
      'tuberculosis' => 'Tuberculosis',
      'sifilis' => 'Sifilis',
      'sífilis' => 'Sifilis',
      'vih' => 'VIH',
      'hepatitis' => 'Hepatitis',
      'sarampion' => 'Sarampion',
      'sarampion' => 'Sarampion',
      'sarampión' => 'Sarampion',
      'dengue' => 'Dengue',
    ];

    $rows = [];

    foreach ($notifiable as $needle => $label) {
      $count = MedicalRecord::whereNotNull('diagnosis')
        ->whereRaw('LOWER(diagnosis) LIKE ?', ['%' . $needle . '%'])
        ->count();

      if ($count > 0) {
        $rows[] = [
          'disease' => $label,
          'count' => $count,
        ];
      }
    }

    usort($rows, fn(array $a, array $b) => $b['count'] <=> $a['count']);

    return $rows;
  }

  private function getTopDiagnoses(): array
  {
    return MedicalRecord::select('diagnosis', DB::raw('COUNT(*) as count'))
      ->whereNotNull('diagnosis')
      ->groupBy('diagnosis')
      ->orderByDesc('count')
      ->limit(15)
      ->get()
      ->map(fn($row) => [
        'diagnosis' => (string) $row->diagnosis,
        'count' => (int) $row->count,
      ])
      ->values()
      ->all();
  }

  private function getAgeDistribution(): array
  {
    $ranges = [
      ['range' => '0-10', 'min' => 0, 'max' => 10],
      ['range' => '11-20', 'min' => 11, 'max' => 20],
      ['range' => '21-30', 'min' => 21, 'max' => 30],
      ['range' => '31-40', 'min' => 31, 'max' => 40],
      ['range' => '41-50', 'min' => 41, 'max' => 50],
      ['range' => '51-60', 'min' => 51, 'max' => 60],
      ['range' => '60+', 'min' => 61, 'max' => 200],
    ];

    $counts = array_fill_keys(array_column($ranges, 'range'), 0);

    $patients = Patient::whereNotNull('birth_date')->get();
    foreach ($patients as $patient) {
      $age = $patient->birth_date?->age;
      if ($age === null) {
        continue;
      }

      foreach ($ranges as $range) {
        if ($age >= $range['min'] && $age <= $range['max']) {
          $counts[$range['range']]++;
          break;
        }
      }
    }

    $total = array_sum($counts);

    return array_map(function (array $range) use ($counts, $total) {
      $count = $counts[$range['range']] ?? 0;
      return [
        'range' => $range['range'],
        'count' => $count,
        'percentage' => $total > 0 ? ($count / $total) * 100 : 0,
      ];
    }, $ranges);
  }

  private function getGenderDistribution(): array
  {
    $raw = Patient::select('gender', DB::raw('COUNT(*) as total'))
      ->whereNotNull('gender')
      ->groupBy('gender')
      ->pluck('total', 'gender')
      ->toArray();

    $distribution = [
      'M' => 0,
      'F' => 0,
      'O' => 0,
    ];

    foreach ($raw as $gender => $total) {
      $distribution[$gender] = (int) $total;
    }

    return $distribution;
  }

  private function getAverageWaitTime(): float
  {
    $appointments = Appointment::whereNotNull('checked_in_at')
      ->whereNotNull('scheduled_at')
      ->get(['scheduled_at', 'checked_in_at']);

    if ($appointments->isEmpty()) {
      return 0;
    }

    $sum = $appointments->sum(function (Appointment $appointment) {
      return max(0, $appointment->scheduled_at->diffInMinutes($appointment->checked_in_at, false));
    });

    return round($sum / $appointments->count(), 2);
  }

  private function getPatientSatisfaction(): float
  {
    return 0.85;
  }

  private function getEmployeeProductivity(): float
  {
    $total = Appointment::count();
    if ($total === 0) {
      return 0;
    }

    $completed = Appointment::where('status', 'completed')->count();
    return $completed / $total;
  }

  private function getAppointmentUtilization(): float
  {
    $total = Appointment::count();
    if ($total === 0) {
      return 0;
    }

    $completed = Appointment::where('status', 'completed')->count();
    return $completed / $total;
  }

  private function getRevenuePerDoctor(): float
  {
    $doctorIds = User::where('role', 'doctor')->pluck('id');
    $doctorCount = $doctorIds->count();

    if ($doctorCount === 0) {
      return 0;
    }

    $revenue = Invoice::whereHas('appointment', function ($query) use ($doctorIds) {
      $query->whereIn('doctor_id', $doctorIds);
    })->sum('total');

    return round((float) $revenue / $doctorCount, 2);
  }

  private function getEmergencyResponseTime(): float
  {
    $admissions = EmergencyAdmission::whereNotNull('admission_time')
      ->whereNotNull('triage_time')
      ->get(['admission_time', 'triage_time']);

    if ($admissions->isEmpty()) {
      return 0;
    }

    $sum = $admissions->sum(function (EmergencyAdmission $admission) {
      return max(0, $admission->admission_time->diffInMinutes($admission->triage_time, false));
    });

    return round($sum / $admissions->count(), 2);
  }

  private function getBillingTrend(): array
  {
    $trend = [];

    for ($i = 5; $i >= 0; $i--) {
      $date = now()->subMonths($i);
      $start = $date->copy()->startOfMonth();
      $end = $date->copy()->endOfMonth();

      $trend[] = [
        'month' => $date->format('m/Y'),
        'amount' => (float) Invoice::whereBetween('created_at', [$start, $end])->sum('total'),
      ];
    }

    return $trend;
  }

  private function getAgingAnalysis(): array
  {
    $ranges = [
      ['range' => '0-30 dias', 'from' => now()->subDays(30), 'to' => now()],
      ['range' => '31-60 dias', 'from' => now()->subDays(60), 'to' => now()->subDays(31)],
      ['range' => '61-90 dias', 'from' => now()->subDays(90), 'to' => now()->subDays(61)],
      ['range' => '90+ dias', 'from' => null, 'to' => now()->subDays(91)],
    ];

    $rows = [];
    $totalPending = 0.0;

    foreach ($ranges as $range) {
      $query = Invoice::whereIn('status', ['pending', 'partially_paid']);

      if ($range['from'] && $range['to']) {
        $query->whereBetween('created_at', [$range['from'], $range['to']]);
      } elseif ($range['to']) {
        $query->where('created_at', '<=', $range['to']);
      }

      $amount = (float) $query->sum('total');
      $totalPending += $amount;

      $rows[] = [
        'range' => $range['range'],
        'amount' => $amount,
        'percentage' => 0.0,
      ];
    }

    foreach ($rows as &$row) {
      $row['percentage'] = $totalPending > 0 ? ($row['amount'] / $totalPending) * 100 : 0;
    }

    return $rows;
  }

  private function getCurrentBedOccupancyRatio(): float
  {
    $totalBeds = Bed::active()->count();
    if ($totalBeds === 0) {
      return 0;
    }

    $occupiedBeds = Bed::where('status', Bed::STATUS_OCCUPIED)->count();
    return $occupiedBeds / $totalBeds;
  }

  private function getAverageLengthOfStay(): float
  {
    $hospitalizations = Hospitalization::whereNotNull('actual_discharge_date')
      ->whereNotNull('admission_date')
      ->get(['admission_date', 'actual_discharge_date']);

    if ($hospitalizations->isEmpty()) {
      return 0;
    }

    $sumDays = $hospitalizations->sum(function (Hospitalization $hospitalization) {
      return $hospitalization->admission_date->diffInDays($hospitalization->actual_discharge_date);
    });

    return round($sumDays / $hospitalizations->count(), 2);
  }

  private function getBedTurnoverRate(): float
  {
    $totalBeds = Bed::active()->count();
    if ($totalBeds === 0) {
      return 0;
    }

    $discharged = Hospitalization::where('status', Hospitalization::STATUS_DISCHARGED)->count();
    return round($discharged / $totalBeds, 2);
  }
}
