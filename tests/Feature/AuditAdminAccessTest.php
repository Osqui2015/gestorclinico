<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditAdminAccessTest extends TestCase
{
  use RefreshDatabase;

  public function test_admin_can_view_audit_index()
  {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
      ->get(route('admin.audits.index'))
      ->assertStatus(200);
  }

  public function test_non_admin_cannot_view_audits()
  {
    $user = User::factory()->create(['role' => 'doctor']);

    $this->actingAs($user)
      ->get(route('admin.audits.index'))
      ->assertStatus(403);
  }
}
