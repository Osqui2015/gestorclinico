<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use App\Models\Audit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditCreationTest extends TestCase
{
  use RefreshDatabase;

  public function test_creating_medical_record_generates_audit()
  {
    $doctor = User::factory()->create(['role' => 'doctor']);
    $patient = Patient::factory()->create();

    $this->actingAs($doctor)
      ->post(route('patients.medical-records.store', $patient->id), [
        'reason' => 'Dolor de cabeza',
        'diagnosis' => 'Migraña probable',
        'treatment' => 'Paracetamol',
      ])
      ->assertRedirect(route('patients.show', $patient->id));

    $this->assertDatabaseHas('medical_records', [
      'patient_id' => $patient->id,
      'doctor_id' => $doctor->id,
      'reason' => 'Dolor de cabeza',
    ]);

    $this->assertDatabaseHas('audits', [
      'user_id' => $doctor->id,
      'event' => 'created',
    ], 'mysql');
  }
}
