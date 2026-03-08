<?php

namespace Database\Seeders;

use App\Models\MaintenanceOrder;
use App\Models\MedicalEquipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaintenanceDemoSeeder extends Seeder
{
  public function run(): void
  {
    $this->command->info('Creando datos de demostracion para mantenimiento...');

    $technician = User::firstOrCreate(
      ['email' => 'mantenimiento@clinica.com'],
      [
        'name' => 'Tecnico de Mantenimiento',
        'password' => bcrypt('password'),
        'role' => 'maintenance',
      ]
    );

    $equipments = [
      [
        'name' => 'Monitor Multiparametrico UCI 01',
        'code' => 'EQ-MON-001',
        'category' => 'monitoring',
        'brand' => 'Mindray',
        'model' => 'BeneVision N12',
        'location' => 'UCI - Box 1',
        'status' => 'operational',
      ],
      [
        'name' => 'Ventilador Mecanico Adulto 02',
        'code' => 'EQ-VENT-002',
        'category' => 'life_support',
        'brand' => 'Drager',
        'model' => 'Evita V300',
        'location' => 'UCI - Box 2',
        'status' => 'maintenance_required',
      ],
      [
        'name' => 'Ecografo Portatil Guardia',
        'code' => 'EQ-IMG-003',
        'category' => 'imaging',
        'brand' => 'Philips',
        'model' => 'Lumify',
        'location' => 'Guardia',
        'status' => 'in_maintenance',
      ],
      [
        'name' => 'Bomba de Infusion Pediatria',
        'code' => 'EQ-INF-004',
        'category' => 'life_support',
        'brand' => 'Baxter',
        'model' => 'Sigma Spectrum',
        'location' => 'Pediatria',
        'status' => 'operational',
      ],
    ];

    $createdEquipments = collect();

    foreach ($equipments as $equipment) {
      $record = MedicalEquipment::firstOrCreate(
        ['code' => $equipment['code']],
        [
          ...$equipment,
          'created_by' => $technician->id,
          'next_maintenance_at' => now()->addDays(45),
        ]
      );

      $createdEquipments->push($record);
    }

    if ($createdEquipments->count() >= 3) {
      MaintenanceOrder::firstOrCreate(
        [
          'medical_equipment_id' => $createdEquipments[1]->id,
          'title' => 'Revision preventiva de ventilador',
          'reported_at' => now()->subDays(2),
        ],
        [
          'reported_by' => $technician->id,
          'assigned_to' => $technician->id,
          'description' => 'Control de presion, sensores y bateria.',
          'priority' => 'high',
          'status' => 'open',
        ]
      );

      MaintenanceOrder::firstOrCreate(
        [
          'medical_equipment_id' => $createdEquipments[2]->id,
          'title' => 'Calibracion ecografo',
          'reported_at' => now()->subDay(),
        ],
        [
          'reported_by' => $technician->id,
          'assigned_to' => $technician->id,
          'description' => 'Ajuste de imagen y verificacion de transductor.',
          'priority' => 'critical',
          'status' => 'in_progress',
          'started_at' => now()->subHours(8),
        ]
      );
    }

    $this->command->info('Datos demo de mantenimiento creados.');
  }
}
