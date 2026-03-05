<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SecretarySeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    User::updateOrCreate(
      ['email' => 'secretaria@clinica.com'],
      [
        'name' => 'Secretaria Principal',
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
        'role' => 'secretary',
      ]
    );
  }
}
