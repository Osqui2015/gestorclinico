<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\HealthInsurance;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SecretaryController extends Controller
{
  /**
   * Display a listing of turns (appointments)
   */
  public function indexTurns()
  {
    $appointments = Appointment::query()
      ->with([
        'doctor',
        'patient.healthInsurances' => fn($query) => $query
          ->select('health_insurances.id', 'health_insurances.name')
          ->wherePivot('is_primary', true),
      ])
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
      ->paginate(15);

    $doctors = User::where('role', 'doctor')->pluck('name', 'id');

    return Inertia::render('Secretary/Turns/Index', [
      'appointments' => $appointments,
      'doctors' => $doctors,
      'filters' => request()->only(['status', 'doctor_id', 'search']),
    ]);
  }

  /**
   * Show the form for creating a new turn
   */
  public function createTurn()
  {
    $patients = Patient::query()
      ->with([
        'healthInsurances' => fn($query) => $query
          ->select('health_insurances.id', 'health_insurances.name')
          ->wherePivot('is_primary', true),
      ])
      ->select('id', 'first_name', 'last_name', 'dni')
      ->get()
      ->mapWithKeys(function ($patient) {
        $primaryInsurance = $patient->healthInsurances->first();
        $insuranceLabel = $primaryInsurance
          ? "OS: {$primaryInsurance->name}"
          : 'OS: Sin obra social';

        if ($primaryInsurance && !empty($primaryInsurance->pivot?->member_number)) {
          $insuranceLabel .= " | Afiliado: {$primaryInsurance->pivot->member_number}";
        }

        return [
          $patient->id => "{$patient->first_name} {$patient->last_name} (DNI: {$patient->dni}) - {$insuranceLabel}"
        ];
      });

    $doctors = User::where('role', 'doctor')
      ->select('id', 'name', 'specialty')
      ->get()
      ->mapWithKeys(fn($doctor) => [
        $doctor->id => "{$doctor->name} - {$doctor->specialty}"
      ]);

    return Inertia::render('Secretary/Turns/Create', [
      'patients' => $patients,
      'doctors' => $doctors,
    ]);
  }

  /**
   * Store a newly created turn
   */
  public function storeTurn(Request $request)
  {
    $validated = $request->validate([
      'doctor_id' => 'required|exists:users,id',
      'patient_id' => 'required|exists:patients,id',
      'scheduled_at' => 'required|date_format:Y-m-d\TH:i|after:now',
      'reason' => 'nullable|string|max:255',
      'notes' => 'nullable|string|max:1000',
    ]);

    // Verify no duplicate appointments for same doctor at same time
    $exists = Appointment::where('doctor_id', $validated['doctor_id'])
      ->where('scheduled_at', $validated['scheduled_at'])
      ->exists();

    if ($exists) {
      return back()->withErrors(['scheduled_at' => 'El médico ya tiene un turno a esa hora']);
    }

    Appointment::create($validated);

    return redirect()->route('secretary.turns.index')
      ->with('success', 'Turno creado exitosamente');
  }

  /**
   * Show the form for editing a turn
   */
  public function editTurn(Appointment $appointment)
  {
    $patients = Patient::query()
      ->with([
        'healthInsurances' => fn($query) => $query
          ->select('health_insurances.id', 'health_insurances.name')
          ->wherePivot('is_primary', true),
      ])
      ->select('id', 'first_name', 'last_name', 'dni')
      ->get()
      ->mapWithKeys(function ($patient) {
        $primaryInsurance = $patient->healthInsurances->first();
        $insuranceLabel = $primaryInsurance
          ? "OS: {$primaryInsurance->name}"
          : 'OS: Sin obra social';

        if ($primaryInsurance && !empty($primaryInsurance->pivot?->member_number)) {
          $insuranceLabel .= " | Afiliado: {$primaryInsurance->pivot->member_number}";
        }

        return [
          $patient->id => "{$patient->first_name} {$patient->last_name} (DNI: {$patient->dni}) - {$insuranceLabel}"
        ];
      });

    $doctors = User::where('role', 'doctor')
      ->select('id', 'name', 'specialty')
      ->get()
      ->mapWithKeys(fn($doctor) => [
        $doctor->id => "{$doctor->name} - {$doctor->specialty}"
      ]);

    return Inertia::render('Secretary/Turns/Edit', [
      'appointment' => $appointment->load(['doctor', 'patient']),
      'patients' => $patients,
      'doctors' => $doctors,
    ]);
  }

  /**
   * Update a turn
   */
  public function updateTurn(Request $request, Appointment $appointment)
  {
    $validated = $request->validate([
      'doctor_id' => 'required|exists:users,id',
      'patient_id' => 'required|exists:patients,id',
      'scheduled_at' => 'required|date_format:Y-m-d\TH:i',
      'reason' => 'nullable|string|max:255',
      'notes' => 'nullable|string|max:1000',
    ]);

    // Verify no duplicate appointments for same doctor at same time (excluding this appointment)
    $exists = Appointment::where('doctor_id', $validated['doctor_id'])
      ->where('scheduled_at', $validated['scheduled_at'])
      ->where('id', '!=', $appointment->id)
      ->exists();

    if ($exists) {
      return back()->withErrors(['scheduled_at' => 'El médico ya tiene un turno a esa hora']);
    }

    $appointment->update($validated);

    return redirect()->route('secretary.turns.index')
      ->with('success', 'Turno actualizado exitosamente');
  }

  /**
   * Delete a turn
   */
  public function destroyTurn(Appointment $appointment)
  {
    $appointment->delete();

    return redirect()->route('secretary.turns.index')
      ->with('success', 'Turno eliminado exitosamente');
  }

  /**
   * Show form for creating a new patient
   */
  public function createPatient()
  {
    $healthInsurances = HealthInsurance::query()
      ->orderBy('name')
      ->get(['id', 'name']);

    return Inertia::render('Secretary/Patients/Create', [
      'healthInsurances' => $healthInsurances,
    ]);
  }

  /**
   * Store a newly created patient
   */
  public function storePatient(Request $request)
  {
    $validated = $request->validate([
      'first_name' => 'required|string|max:100',
      'last_name' => 'required|string|max:100',
      'dni' => 'required|string|max:20|unique:patients,dni',
      'cuil' => 'required|string|unique:patients,cuil|regex:/^\d{2}-?\d{8}-?\d$/',
      'birth_date' => 'required|date|before:today',
      'phone' => 'nullable|string|max:20',
      'email' => 'nullable|email|max:100',
      'medical_history' => 'nullable|string',
      'health_insurance_id' => 'nullable|exists:health_insurances,id',
      'member_number' => 'nullable|string|max:50',
      'new_health_insurance_name' => 'nullable|string|max:255',
      'new_health_insurance_code' => 'nullable|string|max:50',
    ]);

    $healthInsuranceId = $validated['health_insurance_id'] ?? null;
    $memberNumber = $validated['member_number'] ?? null;
    $newHealthInsuranceName = $validated['new_health_insurance_name'] ?? null;
    $newHealthInsuranceCode = $validated['new_health_insurance_code'] ?? null;
    unset(
      $validated['health_insurance_id'],
      $validated['member_number'],
      $validated['new_health_insurance_name'],
      $validated['new_health_insurance_code']
    );

    if ($newHealthInsuranceName) {
      $healthInsurance = HealthInsurance::firstOrCreate(
        ['name' => trim($newHealthInsuranceName)],
        [
          'code' => $newHealthInsuranceCode ? trim($newHealthInsuranceCode) : null,
          'is_active' => true,
          'notes' => 'Creada manualmente desde formulario de secretaría',
        ]
      );

      if ($newHealthInsuranceCode && !$healthInsurance->code) {
        $healthInsurance->update(['code' => trim($newHealthInsuranceCode)]);
      }

      $healthInsuranceId = $healthInsurance->id;
    }

    $patient = Patient::create($validated);

    if ($healthInsuranceId) {
      $patient->healthInsurances()->sync([
        $healthInsuranceId => [
          'is_primary' => true,
          'member_number' => $memberNumber,
        ],
      ]);
    }

    return redirect()->route('secretary.turns.create')
      ->with('success', "Paciente {$patient->first_name} {$patient->last_name} creado exitosamente");
  }
}
