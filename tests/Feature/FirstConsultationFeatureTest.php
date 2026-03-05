<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\MedicalRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FirstConsultationFeatureTest extends TestCase
{
  use RefreshDatabase;

  public function test_first_consultation_is_detected()
  {
    // Create a doctor and a patient
    $doctor = User::factory()->create(['role' => 'doctor']);
    $patient = Patient::factory()->create();

    // Act as the doctor
    $this->actingAs($doctor);

    // Request the create form for a new medical record
    $response = $this->get(route('patients.medical-records.create', $patient->id));

    // Assert the response is successful
    $response->assertStatus(200);

    // Assert isFirstConsultation is true (no prior records)
    $response->assertInertia(
      fn(Assert $page) =>
      $page->where('isFirstConsultation', true)
    );
  }

  public function test_first_consultation_antecedents_can_be_saved()
  {
    // Create a doctor and a patient
    $doctor = User::factory()->create(['role' => 'doctor']);
    $patient = Patient::factory()->create();

    // Act as the doctor
    $this->actingAs($doctor);

    // Create a medical record with antecedents
    $response = $this->post(
      route('patients.medical-records.store', $patient->id),
      [
        'reason' => 'Consulta inicial',
        'diagnosis' => 'Diagnóstico de prueba',
        'treatment' => 'Tratamiento de prueba',
        'private_notes' => 'Notas privadas',
        'is_first_consultation' => true,
        'health_background' => 'Antecedentes de salud relevantes del paciente',
        'prescriptions' => [],
      ]
    );

    // Assert the response redirects to patient show
    $response->assertRedirect(route('patients.show', $patient->id));

    // Assert the medical record was created
    $medicalRecord = MedicalRecord::where('patient_id', $patient->id)->first();
    $this->assertNotNull($medicalRecord);
    $this->assertTrue($medicalRecord->is_first_consultation);
    $this->assertEquals('Antecedentes de salud relevantes del paciente', $medicalRecord->health_background);
  }

  public function test_first_consultation_flag_is_false_for_repeat_visits()
  {
    // Create a doctor and a patient
    $doctor = User::factory()->create(['role' => 'doctor']);
    $patient = Patient::factory()->create();

    // Create a first medical record
    MedicalRecord::factory()->create([
      'patient_id' => $patient->id,
      'doctor_id' => $doctor->id,
      'is_first_consultation' => true,
      'health_background' => 'Antecedentes previos',
    ]);

    // Act as the doctor
    $this->actingAs($doctor);

    // Request the create form for a second medical record
    $response = $this->get(route('patients.medical-records.create', $patient->id));

    // Assert the response is successful
    $response->assertStatus(200);

    // Assert isFirstConsultation is false (there are prior records)
    $response->assertInertia(
      fn(Assert $page) =>
      $page->where('isFirstConsultation', false)
    );
  }

  public function test_health_background_is_required_if_first_consultation()
  {
    // Create a doctor and a patient
    $doctor = User::factory()->create(['role' => 'doctor']);
    $patient = Patient::factory()->create();

    // Act as the doctor
    $this->actingAs($doctor);

    // Try to create a medical record with first_consultation=true but no health_background
    $response = $this->post(
      route('patients.medical-records.store', $patient->id),
      [
        'reason' => 'Consulta inicial',
        'diagnosis' => 'Diagnóstico de prueba',
        'treatment' => 'Tratamiento de prueba',
        'private_notes' => 'Notas privadas',
        'is_first_consultation' => true,
        'health_background' => '',  // Empty health background
        'prescriptions' => [],
      ]
    );

    // Assert validation fails
    $response->assertRedirect();
    $response->assertSessionHasErrors('health_background');
  }

  public function test_health_background_is_optional_if_not_first_consultation()
  {
    // Create a doctor and a patient
    $doctor = User::factory()->create(['role' => 'doctor']);
    $patient = Patient::factory()->create();

    // Act as the doctor
    $this->actingAs($doctor);

    // Create a medical record without is_first_consultation flag and without health_background
    $response = $this->post(
      route('patients.medical-records.store', $patient->id),
      [
        'reason' => 'Consulta de seguimiento',
        'diagnosis' => 'Diagnóstico de prueba',
        'treatment' => 'Tratamiento de prueba',
        'private_notes' => 'Notas privadas',
        'is_first_consultation' => false,
        'health_background' => '',  // Empty is allowed
        'prescriptions' => [],
      ]
    );

    // Assert the response redirects to patient show
    $response->assertRedirect(route('patients.show', $patient->id));

    // Assert the medical record was created
    $medicalRecord = MedicalRecord::where('patient_id', $patient->id)->first();
    $this->assertNotNull($medicalRecord);
    $this->assertFalse($medicalRecord->is_first_consultation);
  }

  public function test_medical_record_stores_health_background_correctly()
  {
    // Create a doctor and a patient
    $doctor = User::factory()->create(['role' => 'doctor']);
    $patient = Patient::factory()->create();

    // Act as the doctor
    $this->actingAs($doctor);

    $backgroundText = <<<'EOT'
Antecedentes médicos:
- Alergia a penicilina
- Cirugía de apéndice en 2018
- Medicación crónica: Metformina 500mg x 2
- Antecedentes familiares: Diabetes tipo 2
EOT;

    // Create a medical record with detailed antecedents
    $response = $this->post(
      route('patients.medical-records.store', $patient->id),
      [
        'reason' => 'Consulta inicial',
        'diagnosis' => 'Evaluación general',
        'treatment' => 'Seguimiento',
        'private_notes' => 'Notas privadas',
        'is_first_consultation' => true,
        'health_background' => $backgroundText,
        'prescriptions' => [],
      ]
    );

    // Assert the response redirects successfully
    $response->assertRedirect();

    // Assert the medical record was created with correct antecedents
    $medicalRecord = MedicalRecord::where('patient_id', $patient->id)->first();
    $this->assertNotNull($medicalRecord);
    $this->assertStringContainsString('Alergia a penicilina', $medicalRecord->health_background);
    $this->assertStringContainsString('Diabetes tipo 2', $medicalRecord->health_background);
  }
}
