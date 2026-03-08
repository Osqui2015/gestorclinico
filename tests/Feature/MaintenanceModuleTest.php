<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModuleTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();
    $this->withoutVite();
  }

  public function test_maintenance_user_can_access_dashboard(): void
  {
    $maintenance = User::factory()->create(['role' => 'maintenance']);

    $this->actingAs($maintenance)
      ->get(route('maintenance.index'))
      ->assertOk();
  }

  public function test_non_maintenance_user_cannot_access_dashboard(): void
  {
    $doctor = User::factory()->create(['role' => 'doctor']);

    $this->actingAs($doctor)
      ->get(route('maintenance.index'))
      ->assertStatus(403);
  }

  public function test_maintenance_user_can_store_equipment(): void
  {
    $maintenance = User::factory()->create(['role' => 'maintenance']);

    $payload = [
      'name' => 'Monitor Test UCI',
      'code' => 'EQ-TEST-001',
      'category' => 'monitoring',
      'brand' => 'Mindray',
      'model' => 'N12',
      'location' => 'UCI',
      'status' => 'operational',
    ];

    $this->actingAs($maintenance)
      ->post(route('maintenance.equipments.store'), $payload)
      ->assertRedirect(route('maintenance.index'));

    $this->assertDatabaseHas('medical_equipments', [
      'code' => 'EQ-TEST-001',
      'name' => 'Monitor Test UCI',
      'created_by' => $maintenance->id,
    ]);
  }
}
