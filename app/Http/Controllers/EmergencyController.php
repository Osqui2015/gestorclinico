<?php

namespace App\Http\Controllers;

use App\Events\EmergencyBoardUpdated;
use App\Models\EmergencyAdmission;
use App\Models\EmergencyEvolution;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EmergencyController extends Controller
{
  private function authUser(): User
  {
    /** @var User|null $user */
    $user = Auth::user();
    if (!$user instanceof User) {
      abort(401, 'Usuario no autenticado.');
    }
    return $user;
  }

  private function parseBloodPressure(?string $bloodPressure, mixed $systolic = null, mixed $diastolic = null): array
  {
    $systolicValue = is_numeric($systolic) ? (float) $systolic : null;
    $diastolicValue = is_numeric($diastolic) ? (float) $diastolic : null;

    if ($systolicValue !== null || $diastolicValue !== null) {
      return [
        'systolic_pressure' => $systolicValue,
        'diastolic_pressure' => $diastolicValue,
      ];
    }

    if (!$bloodPressure) {
      return [
        'systolic_pressure' => null,
        'diastolic_pressure' => null,
      ];
    }

    if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)\s*$/', $bloodPressure, $matches)) {
      return [
        'systolic_pressure' => (float) $matches[1],
        'diastolic_pressure' => (float) $matches[2],
      ];
    }

    if (is_numeric($bloodPressure)) {
      return [
        'systolic_pressure' => (float) $bloodPressure,
        'diastolic_pressure' => null,
      ];
    }

    return [
      'systolic_pressure' => null,
      'diastolic_pressure' => null,
    ];
  }

  private function formatVital(?float $value): ?string
  {
    if ($value === null) {
      return null;
    }

    if (fmod($value, 1.0) === 0.0) {
      return (string) (int) $value;
    }

    return (string) round($value, 2);
  }

  private function formatBloodPressure(?float $systolic, ?float $diastolic): ?string
  {
    if ($systolic === null && $diastolic === null) {
      return null;
    }

    if ($systolic !== null && $diastolic !== null) {
      return $this->formatVital($systolic) . '/' . $this->formatVital($diastolic);
    }

    return $this->formatVital($systolic ?? $diastolic);
  }

  private function mapEvolutionForUi(EmergencyEvolution $evolution): array
  {
    return [
      'id' => $evolution->id,
      'recorded_at' => $evolution->recorded_at?->toIso8601String(),
      'blood_pressure' => $this->formatBloodPressure($evolution->systolic_pressure, $evolution->diastolic_pressure),
      'heart_rate' => $evolution->heart_rate,
      'respiratory_rate' => $evolution->respiratory_rate,
      'temperature' => $evolution->temperature,
      'oxygen_saturation' => $evolution->oxygen_saturation,
      'glucose_level' => $evolution->glucose,
      'clinical_notes' => $evolution->clinical_notes,
      'treatment_notes' => $evolution->treatment_notes,
      'medications_given' => $evolution->medications_given,
      'tests_performed' => $evolution->tests_performed,
      'recorded_by' => $evolution->recordedBy ? [
        'id' => $evolution->recordedBy->id,
        'name' => $evolution->recordedBy->name,
      ] : null,
    ];
  }

  private function mapAdmissionForUi(EmergencyAdmission $admission, bool $withEvolutions = false): array
  {
    $data = [
      'id' => $admission->id,
      'admission_time' => $admission->admission_time?->toIso8601String(),
      'triage_time' => $admission->triage_time?->toIso8601String(),
      'discharged_at' => $admission->discharged_at?->toIso8601String(),
      'triage_level' => (int) ($admission->triage_level ?? 5),
      'chief_complaint' => $admission->chief_complaint,
      'blood_pressure' => $this->formatBloodPressure($admission->systolic_pressure, $admission->diastolic_pressure),
      'heart_rate' => $admission->heart_rate,
      'respiratory_rate' => $admission->respiratory_rate,
      'temperature' => $admission->temperature,
      'oxygen_saturation' => $admission->oxygen_saturation,
      'glucose_level' => $admission->glucose,
      'consciousness_level' => $admission->consciousness_level,
      'status' => $admission->status,
      'diagnosis' => $admission->preliminary_diagnosis,
      'treatment' => $admission->treatment_given,
      'discharge_instructions' => $admission->discharge_instructions,
      'observations' => $admission->observations,
      'patient' => $admission->patient ? [
        'id' => $admission->patient->id,
        'first_name' => $admission->patient->first_name,
        'last_name' => $admission->patient->last_name,
        'dni' => $admission->patient->dni,
        'phone' => $admission->patient->phone,
        'address' => $admission->patient->address,
      ] : null,
      'attending_doctor' => $admission->attendingDoctor ? [
        'id' => $admission->attendingDoctor->id,
        'name' => $admission->attendingDoctor->name,
      ] : null,
    ];

    if ($withEvolutions) {
      $data['evolutions'] = $admission->evolutions
        ->sortByDesc('recorded_at')
        ->map(fn(EmergencyEvolution $e) => $this->mapEvolutionForUi($e))
        ->values();
    }

    return $data;
  }

  /**
   * Tablero de emergencias
   */
  public function board()
  {
    $admissions = EmergencyAdmission::with(['patient', 'attendingDoctor'])
      ->whereIn('status', ['waiting', 'in_care', 'observation'])
      ->orderBy('triage_level')
      ->orderBy('admission_time')
      ->get()
      ->map(fn(EmergencyAdmission $admission) => $this->mapAdmissionForUi($admission));

    return Inertia::render('Emergency/Board', [
      'admissions' => $admissions,
    ]);
  }

  /**
   * Crear nuevo ingreso a emergencia
   */
  public function create()
  {
    $patients = Patient::select(['id', 'first_name', 'last_name', 'dni'])
      ->orderBy('last_name')
      ->orderBy('first_name')
      ->get();

    $doctors = User::where('role', 'doctor')->orderBy('name')->get();

    return Inertia::render('Emergency/Create', [
      'patients' => $patients,
      'doctors' => $doctors,
    ]);
  }

  /**
   * Guardar nuevo ingreso a emergencia
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'patient_id' => 'required|exists:patients,id',
      'attending_doctor_id' => 'nullable|exists:users,id',
      'chief_complaint' => 'required|string|max:255',
      'triage_level' => 'required|in:1,2,3,4,5',
      'blood_pressure' => 'nullable|string|max:20',
      'systolic_pressure' => 'nullable|numeric',
      'diastolic_pressure' => 'nullable|numeric',
      'heart_rate' => 'nullable|numeric',
      'respiratory_rate' => 'nullable|numeric',
      'temperature' => 'nullable|numeric',
      'oxygen_saturation' => 'nullable|numeric',
      'glucose_level' => 'nullable|numeric',
      'glucose' => 'nullable|numeric',
      'consciousness_level' => 'nullable|string',
      'diagnosis' => 'nullable|string',
      'treatment' => 'nullable|string',
      'observations' => 'nullable|string',
      'preliminary_diagnosis' => 'nullable|string',
      'treatment_given' => 'nullable|string',
      'triage_notes' => 'nullable|string',
    ]);

    $user = $this->authUser();

    $pressure = $this->parseBloodPressure(
      $validated['blood_pressure'] ?? null,
      $validated['systolic_pressure'] ?? null,
      $validated['diastolic_pressure'] ?? null,
    );

    $attendingDoctorId = $validated['attending_doctor_id'] ?? null;
    if ($attendingDoctorId === null && $user->role === 'doctor') {
      $attendingDoctorId = $user->id;
    }

    $admission = EmergencyAdmission::create([
      'admission_time' => now(),
      'triage_time' => now(),
      'patient_id' => $validated['patient_id'],
      'attending_doctor_id' => $attendingDoctorId,
      'chief_complaint' => $validated['chief_complaint'],
      'triage_level' => (string) $validated['triage_level'],
      'systolic_pressure' => $pressure['systolic_pressure'],
      'diastolic_pressure' => $pressure['diastolic_pressure'],
      'heart_rate' => $validated['heart_rate'] ?? null,
      'respiratory_rate' => $validated['respiratory_rate'] ?? null,
      'temperature' => $validated['temperature'] ?? null,
      'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
      'glucose' => $validated['glucose_level'] ?? $validated['glucose'] ?? null,
      'consciousness_level' => $validated['consciousness_level'] ?? null,
      'preliminary_diagnosis' => $validated['diagnosis'] ?? $validated['preliminary_diagnosis'] ?? null,
      'treatment_given' => $validated['treatment'] ?? $validated['treatment_given'] ?? null,
      'observations' => $validated['observations'] ?? null,
      'triage_notes' => $validated['triage_notes'] ?? null,
      'status' => 'waiting',
    ]);

    EmergencyBoardUpdated::dispatch($admission->id);

    return redirect()->route('emergency.show', $admission)->with('success', 'Paciente ingresado a emergencias');
  }

  /**
   * Ver detalle de admisión
   */
  public function show(EmergencyAdmission $admission)
  {
    $admission->load(['patient', 'attendingDoctor', 'nurse', 'evolutions.recordedBy']);

    // Obtener historial médico del paciente
    $medicalHistory = [];
    if ($admission->patient && method_exists($admission->patient, 'medicalRecords')) {
      $medicalHistory = $admission->patient->medicalRecords()
        ->with('doctor')
        ->latest()
        ->take(10)
        ->get()
        ->map(function ($record) {
          return [
            'id' => $record->id,
            'created_at' => $record->created_at->toIso8601String(),
            'reason' => $record->reason,
            'diagnosis' => $record->diagnosis,
            'treatment' => $record->treatment,
            'doctor_name' => $record->doctor->name ?? 'N/A',
          ];
        })->toArray();
    }

    return Inertia::render('Emergency/Show', [
      'admission' => $this->mapAdmissionForUi($admission, true),
      'medicalHistory' => $medicalHistory,
    ]);
  }

  /**
   * Formulario de evolución
   */
  public function evolution(EmergencyAdmission $admission)
  {
    $admission->load('patient');

    return Inertia::render('Emergency/Evolution', [
      'admission' => [
        'id' => $admission->id,
        'patient' => [
          'first_name' => $admission->patient?->first_name,
          'last_name' => $admission->patient?->last_name,
        ],
      ],
    ]);
  }

  /**
   * Editar admisión
   */
  public function edit(EmergencyAdmission $admission)
  {
    $admission->load(['patient', 'attendingDoctor']);

    $patients = Patient::select(['id', 'first_name', 'last_name', 'dni'])
      ->orderBy('last_name')
      ->orderBy('first_name')
      ->get();

    $doctors = User::where('role', 'doctor')->get();

    return Inertia::render('Emergency/Edit', [
      'admission' => $this->mapAdmissionForUi($admission),
      'patients' => $patients,
      'doctors' => $doctors,
    ]);
  }

  /**
   * Actualizar admisión
   */
  public function update(Request $request, EmergencyAdmission $admission)
  {
    $validated = $request->validate([
      'chief_complaint' => 'string|max:255',
      'triage_level' => 'in:1,2,3,4,5',
      'blood_pressure' => 'nullable|string|max:20',
      'systolic_pressure' => 'nullable|numeric',
      'diastolic_pressure' => 'nullable|numeric',
      'heart_rate' => 'nullable|numeric',
      'respiratory_rate' => 'nullable|numeric',
      'temperature' => 'nullable|numeric',
      'oxygen_saturation' => 'nullable|numeric',
      'glucose_level' => 'nullable|numeric',
      'glucose' => 'nullable|numeric',
      'consciousness_level' => 'nullable|string',
      'diagnosis' => 'nullable|string',
      'treatment' => 'nullable|string',
      'preliminary_diagnosis' => 'nullable|string',
      'treatment_given' => 'nullable|string',
      'clinical_evolution' => 'nullable|string',
      'observations' => 'nullable|string',
      'attending_doctor_id' => 'nullable|exists:users,id',
      'nurse_id' => 'nullable|exists:users,id',
    ]);

    $pressure = $this->parseBloodPressure(
      $validated['blood_pressure'] ?? null,
      $validated['systolic_pressure'] ?? null,
      $validated['diastolic_pressure'] ?? null,
    );

    $payload = [
      'chief_complaint' => $validated['chief_complaint'] ?? $admission->chief_complaint,
      'triage_level' => isset($validated['triage_level']) ? (string) $validated['triage_level'] : $admission->triage_level,
      'systolic_pressure' => $pressure['systolic_pressure'],
      'diastolic_pressure' => $pressure['diastolic_pressure'],
      'heart_rate' => $validated['heart_rate'] ?? null,
      'respiratory_rate' => $validated['respiratory_rate'] ?? null,
      'temperature' => $validated['temperature'] ?? null,
      'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
      'glucose' => $validated['glucose_level'] ?? $validated['glucose'] ?? null,
      'consciousness_level' => $validated['consciousness_level'] ?? null,
      'preliminary_diagnosis' => $validated['diagnosis'] ?? $validated['preliminary_diagnosis'] ?? null,
      'treatment_given' => $validated['treatment'] ?? $validated['treatment_given'] ?? null,
      'clinical_evolution' => $validated['clinical_evolution'] ?? null,
      'observations' => $validated['observations'] ?? null,
      'attending_doctor_id' => $validated['attending_doctor_id'] ?? null,
      'nurse_id' => $validated['nurse_id'] ?? null,
    ];

    $admission->update($payload);

    return redirect()->route('emergency.show', $admission)->with('success', 'Admisión actualizada');
  }

  /**
   * Registrar evolución
   */
  public function recordEvolution(Request $request, EmergencyAdmission $admission)
  {
    $validated = $request->validate([
      'blood_pressure' => 'nullable|string|max:20',
      'systolic_pressure' => 'nullable|numeric',
      'diastolic_pressure' => 'nullable|numeric',
      'heart_rate' => 'nullable|numeric',
      'respiratory_rate' => 'nullable|numeric',
      'temperature' => 'nullable|numeric',
      'oxygen_saturation' => 'nullable|numeric',
      'glucose_level' => 'nullable|numeric',
      'glucose' => 'nullable|numeric',
      'clinical_notes' => 'required|string',
      'treatment_notes' => 'nullable|string',
      'medications_given' => 'nullable|string',
      'tests_performed' => 'nullable|string',
    ]);

    $user = $this->authUser();

    $pressure = $this->parseBloodPressure(
      $validated['blood_pressure'] ?? null,
      $validated['systolic_pressure'] ?? null,
      $validated['diastolic_pressure'] ?? null,
    );

    EmergencyEvolution::create([
      'emergency_admission_id' => $admission->id,
      'recorded_by' => $user->id,
      'recorded_at' => now(),
      'systolic_pressure' => $pressure['systolic_pressure'],
      'diastolic_pressure' => $pressure['diastolic_pressure'],
      'heart_rate' => $validated['heart_rate'] ?? null,
      'respiratory_rate' => $validated['respiratory_rate'] ?? null,
      'temperature' => $validated['temperature'] ?? null,
      'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
      'glucose' => $validated['glucose_level'] ?? $validated['glucose'] ?? null,
      'clinical_notes' => $validated['clinical_notes'],
      'treatment_notes' => $validated['treatment_notes'] ?? null,
      'medications_given' => $validated['medications_given'] ?? null,
      'tests_performed' => $validated['tests_performed'] ?? null,
    ]);

    return redirect()->route('emergency.show', $admission)->with('success', 'Evolución registrada');
  }

  /**
   * Cambiar estado de admisión
   */
  public function changeStatus(Request $request, EmergencyAdmission $admission)
  {
    $validated = $request->validate([
      'status' => 'required|in:waiting,in_care,observation,discharged,admitted,transferred',
      'discharge_diagnosis' => 'required_if:status,discharged|nullable|string',
      'discharge_instructions' => 'required_if:status,discharged|nullable|string',
      'save_to_history' => 'boolean',
    ]);

    $data = ['status' => $validated['status']];

    if ($validated['status'] === 'discharged') {
      $data['discharged_at'] = now();
      $data['discharge_diagnosis'] = $validated['discharge_diagnosis'] ?? null;
      $data['discharge_instructions'] = $validated['discharge_instructions'] ?? null;

      // Guardar automáticamente en historial médico si se especifica
      if ($validated['save_to_history'] ?? true) {
        $this->saveToMedicalHistory($admission, $validated['discharge_diagnosis'], $validated['discharge_instructions']);
      }
    }

    $admission->update($data);

    EmergencyBoardUpdated::dispatch($admission->id);

    return back()->with('success', 'Estado actualizado');
  }

  /**
   * Guardar consulta de emergencia en historial médico
   */
  private function saveToMedicalHistory(EmergencyAdmission $admission, ?string $diagnosis, ?string $instructions): void
  {
    $user = $this->authUser();

    // Compilar información de la consulta de emergencia
    $treatment = [];
    if ($admission->treatment_given) {
      $treatment[] = "Tratamiento: " . $admission->treatment_given;
    }
    if ($instructions) {
      $treatment[] = "Instrucciones: " . $instructions;
    }
    if ($admission->evolutions->count() > 0) {
      $treatment[] = "\nEvoluciones (" . $admission->evolutions->count() . " registradas)";
    }

    \App\Models\MedicalRecord::create([
      'patient_id' => $admission->patient_id,
      'doctor_id' => $admission->attending_doctor_id ?? $user->id,
      'reason' => "EMERGENCIA: " . $admission->chief_complaint,
      'diagnosis' => $diagnosis ?? $admission->preliminary_diagnosis,
      'treatment' => implode("\n", $treatment),
      'private_notes' => "Atención de emergencia. Triage nivel " . $admission->triage_level . ". " . ($admission->observations ?? ''),
      'is_first_consultation' => false,
    ]);
  }

  /**
   * Crear receta desde emergencia
   */
  public function createPrescription(Request $request, EmergencyAdmission $admission)
  {
    $validated = $request->validate([
      'medication_name' => 'required|string|max:255',
      'dosage' => 'required|string|max:255',
      'instructions' => 'nullable|string',
      'quantity' => 'nullable|string|max:100',
      'duration' => 'nullable|string|max:100',
    ]);

    $user = $this->authUser();

    $prescription = \App\Models\Prescription::create([
      'patient_id' => $admission->patient_id,
      'doctor_id' => $admission->attending_doctor_id ?? $user->id,
      'medication_name' => $validated['medication_name'],
      'dosage' => $validated['dosage'],
      'instructions' => $validated['instructions'] ?? null,
      'quantity' => $validated['quantity'] ?? null,
      'duration' => $validated['duration'] ?? null,
      'issued_at' => now(),
    ]);

    return back()->with('success', 'Receta creada exitosamente');
  }

  /**
   * Solicitar medicación a farmacia desde emergencia
   */
  public function requestPharmacy(Request $request, EmergencyAdmission $admission)
  {
    $validated = $request->validate([
      'pharmacy_item_id' => 'required|exists:pharmacy_items,id',
      'quantity' => 'required|integer|min:1',
      'notes' => 'nullable|string',
      'urgency' => 'required|in:normal,urgent,emergency',
    ]);

    $user = $this->authUser();

    $pharmacyRequest = \App\Models\PharmacyRequest::create([
      'doctor_id' => $admission->attending_doctor_id ?? $user->id,
      'patient_id' => $admission->patient_id,
      'pharmacy_item_id' => $validated['pharmacy_item_id'],
      'quantity' => $validated['quantity'],
      'notes' => "EMERGENCIA - " . ($validated['notes'] ?? $admission->chief_complaint),
      'urgency' => $validated['urgency'],
      'status' => 'pending',
      'requested_at' => now(),
    ]);

    return back()->with('success', 'Solicitud a farmacia enviada');
  }

  /**
   * Internar paciente desde emergencia
   */
  public function admitToHospital(Request $request, EmergencyAdmission $admission)
  {
    $validated = $request->validate([
      'bed_id' => 'required|exists:beds,id',
      'admission_diagnosis' => 'required|string',
      'treatment_plan' => 'nullable|string',
      'expected_stay_days' => 'nullable|integer|min:1',
    ]);

    $user = $this->authUser();

    // Verificar que la cama esté disponible
    $bed = \App\Models\Bed::findOrFail($validated['bed_id']);
    if ($bed->status !== \App\Models\Bed::STATUS_AVAILABLE) {
      return back()->with('error', 'La cama seleccionada no está disponible');
    }

    // Crear internación
    $hospitalization = \App\Models\Hospitalization::create([
      'patient_id' => $admission->patient_id,
      'bed_id' => $validated['bed_id'],
      'doctor_id' => $admission->attending_doctor_id ?? $user->id,
      'admission_date' => now(),
      'admission_diagnosis' => $validated['admission_diagnosis'],
      'treatment_plan' => $validated['treatment_plan'] ?? null,
      'expected_discharge_date' => $validated['expected_stay_days']
        ? now()->addDays($validated['expected_stay_days'])
        : null,
      'observations' => "Ingreso desde emergencia. Triage: " . $admission->getTriageLevelName(),
    ]);

    // Actualizar estado de la cama
    $bed->update(['status' => \App\Models\Bed::STATUS_OCCUPIED]);

    // Actualizar estado de la admisión de emergencia
    $admission->update(['status' => 'admitted']);

    return redirect()->route('hospitalizations.show', $bed->id)
      ->with('success', 'Paciente internado exitosamente');
  }

  /**
   * Historial de emergencias
   */
  public function history()
  {
    $admissions = EmergencyAdmission::with(['patient', 'attendingDoctor'])
      ->where('status', 'discharged')
      ->orderByDesc('discharged_at')
      ->paginate(20)
      ->through(fn(EmergencyAdmission $admission) => $this->mapAdmissionForUi($admission));

    return Inertia::render('Emergency/History', [
      'admissions' => $admissions,
    ]);
  }
}
