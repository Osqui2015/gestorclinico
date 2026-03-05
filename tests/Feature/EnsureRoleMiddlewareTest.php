<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureRoleMiddlewareTest extends TestCase
{
  use RefreshDatabase;

  public function test_non_doctor_cannot_access_medical_records_routes()
  {
    $user = User::factory()->create(['role' => 'secretary']);
    $patient = Patient::factory()->create();

    $this->actingAs($user)
      ->get(route('patients.medical-records.create', $patient->id))
      ->assertStatus(403);
  }
}
