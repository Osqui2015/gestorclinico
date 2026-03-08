<?php

namespace Database\Seeders;

use App\Models\Ambulance;
use App\Models\EmergencyTransfer;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class ParamedicDemoSeeder extends Seeder
{
  public function run(): void
  {
    $this->command->info('Creando datos de demostracion para paramedicos y traslados...');

    $paramedic = User::firstOrCreate(
      ['email' => 'paramedico@clinica.com'],
      [
        'name' => 'Paramedico de Guardia',
        'password' => bcrypt('password'),
        'role' => 'paramedic',
      ]
    );

    $mobile1 = Ambulance::firstOrCreate(
      ['internal_code' => 'MOV-01'],
      [
        'plate_number' => 'AB123CD',
        'brand' => 'Mercedes-Benz',
        'model' => 'Sprinter',
        'year' => 2022,
        'base_location' => 'Base Central',
        'status' => 'available',
        'current_mileage' => 52340,
        'created_by' => $paramedic->id,
      ]
    );

    $mobile2 = Ambulance::firstOrCreate(
      ['internal_code' => 'MOV-02'],
      [
        'plate_number' => 'AC456EF',
        'brand' => 'Renault',
        'model' => 'Master',
        'year' => 2021,
        'base_location' => 'Anexo Norte',
        'status' => 'maintenance',
        'current_mileage' => 68410,
        'created_by' => $paramedic->id,
      ]
    );

    $patients = Patient::take(3)->get();

    if ($patients->isEmpty()) {
      $this->command->warn('No hay pacientes para crear traslados de demo.');
      return;
    }

    EmergencyTransfer::firstOrCreate(
      [
        'origin' => 'Clinica Central',
        'destination' => 'Hospital Regional',
        'requested_at' => now()->startOfDay()->addHours(8),
      ],
      [
        'patient_id' => $patients->first()->id,
        'requested_by' => $paramedic->id,
        'ambulance_id' => $mobile1->id,
        'transfer_type' => 'interhospital',
        'priority' => 'high',
        'status' => 'assigned',
        'assigned_at' => now()->startOfDay()->addHours(8)->addMinutes(10),
        'clinical_summary' => 'Paciente post-quirurgico para evaluacion especializada.',
      ]
    );

    EmergencyTransfer::firstOrCreate(
      [
        'origin' => 'Domicilio Paciente',
        'destination' => 'Guardia Clinica',
        'requested_at' => now()->startOfDay()->addHours(10),
      ],
      [
        'patient_id' => $patients->count() > 1 ? $patients[1]->id : $patients->first()->id,
        'requested_by' => $paramedic->id,
        'transfer_type' => 'emergency',
        'priority' => 'critical',
        'status' => 'requested',
        'clinical_summary' => 'Disnea de inicio brusco.',
      ]
    );

    $this->command->info('Datos demo de paramedicos creados.');
  }
}
