<?php

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HospitalizationSetupSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $rooms = [
      [
        'name' => 'Habitacion 201',
        'code' => 'H201',
        'room_type' => Room::TYPE_STANDARD,
        'floor' => 2,
        'wing' => 'Norte',
        'max_beds' => 2,
        'description' => 'Internacion general',
        'is_active' => true,
      ],
      [
        'name' => 'Habitacion 202',
        'code' => 'H202',
        'room_type' => Room::TYPE_STANDARD,
        'floor' => 2,
        'wing' => 'Norte',
        'max_beds' => 2,
        'description' => 'Internacion general',
        'is_active' => true,
      ],
      [
        'name' => 'UCI 301',
        'code' => 'UCI301',
        'room_type' => Room::TYPE_INTENSIVE_CARE,
        'floor' => 3,
        'wing' => 'Sur',
        'max_beds' => 1,
        'description' => 'Unidad de cuidados intensivos',
        'is_active' => true,
      ],
      [
        'name' => 'Aislamiento 401',
        'code' => 'A401',
        'room_type' => Room::TYPE_ISOLATION,
        'floor' => 4,
        'wing' => 'Oeste',
        'max_beds' => 1,
        'description' => 'Sala de aislamiento',
        'is_active' => true,
      ],
    ];

    $roomIdsByCode = [];

    foreach ($rooms as $roomData) {
      $room = Room::updateOrCreate(
        ['code' => $roomData['code']],
        $roomData
      );

      $roomIdsByCode[$room->code] = $room->id;
    }

    $beds = [
      [
        'room_code' => 'H201',
        'bed_number' => 'A',
        'status' => Bed::STATUS_AVAILABLE,
        'bed_type' => Bed::TYPE_STANDARD,
      ],
      [
        'room_code' => 'H201',
        'bed_number' => 'B',
        'status' => Bed::STATUS_AVAILABLE,
        'bed_type' => Bed::TYPE_STANDARD,
      ],
      [
        'room_code' => 'H202',
        'bed_number' => 'A',
        'status' => Bed::STATUS_AVAILABLE,
        'bed_type' => Bed::TYPE_STANDARD,
      ],
      [
        'room_code' => 'H202',
        'bed_number' => 'B',
        'status' => Bed::STATUS_AVAILABLE,
        'bed_type' => Bed::TYPE_STANDARD,
      ],
      [
        'room_code' => 'UCI301',
        'bed_number' => '1',
        'status' => Bed::STATUS_AVAILABLE,
        'bed_type' => Bed::TYPE_INTENSIVE_CARE,
      ],
      [
        'room_code' => 'A401',
        'bed_number' => '1',
        'status' => Bed::STATUS_AVAILABLE,
        'bed_type' => Bed::TYPE_ISOLATION,
      ],
    ];

    foreach ($beds as $bedData) {
      $roomId = $roomIdsByCode[$bedData['room_code']] ?? null;

      if (!$roomId) {
        continue;
      }

      Bed::updateOrCreate(
        [
          'room_id' => $roomId,
          'bed_number' => $bedData['bed_number'],
        ],
        [
          'status' => $bedData['status'],
          'bed_type' => $bedData['bed_type'],
          'is_active' => true,
          'observations' => null,
        ]
      );
    }

    $nurses = [
      [
        'name' => 'Enfermera Jefe',
        'email' => 'enfermeria@clinica.com',
        'phone' => '+54 381 555-9001',
      ],
      [
        'name' => 'Enfermero Turno Noche',
        'email' => 'enfermeria.noche@clinica.com',
        'phone' => '+54 381 555-9002',
      ],
    ];

    foreach ($nurses as $nurseData) {
      User::updateOrCreate(
        ['email' => $nurseData['email']],
        [
          'name' => $nurseData['name'],
          'password' => Hash::make('password'),
          'role' => 'nurse',
          'phone' => $nurseData['phone'],
          'email_verified_at' => now(),
        ]
      );
    }

    $this->command?->info('HospitalizationSetupSeeder: habitaciones, camas y usuarios de enfermeria creados/actualizados.');
  }
}
