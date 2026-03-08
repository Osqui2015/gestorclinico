<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretaryAccessRoutesTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();
    $this->withoutVite();
  }

  public function test_secretary_can_access_health_insurances_and_reception_dashboard(): void
  {
    $secretary = User::factory()->create(['role' => 'secretary']);

    $this->actingAs($secretary)
      ->get(route('health-insurances.index'))
      ->assertOk();

    $this->actingAs($secretary)
      ->get(route('reception.dashboard'))
      ->assertOk();
  }

  public function test_doctor_cannot_access_health_insurances_and_reception_dashboard(): void
  {
    $doctor = User::factory()->create(['role' => 'doctor']);

    $this->actingAs($doctor)
      ->get(route('health-insurances.index'))
      ->assertStatus(403);

    $this->actingAs($doctor)
      ->get(route('reception.dashboard'))
      ->assertStatus(403);
  }
}
