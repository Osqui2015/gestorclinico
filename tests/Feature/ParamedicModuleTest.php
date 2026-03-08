<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParamedicModuleTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();
    $this->withoutVite();
  }

  public function test_paramedic_user_can_access_dashboard(): void
  {
    $paramedic = User::factory()->create(['role' => 'paramedic']);

    $this->actingAs($paramedic)
      ->get(route('paramedic.dashboard'))
      ->assertOk();
  }

  public function test_non_paramedic_user_cannot_access_dashboard(): void
  {
    $secretary = User::factory()->create(['role' => 'secretary']);

    $this->actingAs($secretary)
      ->get(route('paramedic.dashboard'))
      ->assertStatus(403);
  }

  public function test_paramedic_user_can_store_transfer(): void
  {
    $paramedic = User::factory()->create(['role' => 'paramedic']);
    $patient = Patient::factory()->create();

    $payload = [
      'patient_id' => $patient->id,
      'origin' => 'Guardia Clinica',
      'destination' => 'Hospital Regional',
      'transfer_type' => 'interhospital',
      'priority' => 'high',
    ];

    $this->actingAs($paramedic)
      ->post(route('paramedic.transfers.store'), $payload)
      ->assertRedirect(route('paramedic.dashboard'));

    $this->assertDatabaseHas('emergency_transfers', [
      'patient_id' => $patient->id,
      'requested_by' => $paramedic->id,
      'origin' => 'Guardia Clinica',
      'destination' => 'Hospital Regional',
      'status' => 'requested',
    ]);
  }
}
