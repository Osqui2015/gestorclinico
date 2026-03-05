<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\DoctorSchedule;

echo "Creando horarios por defecto para doctores...\n\n";

$doctors = User::where('role', 'doctor')->get();

if ($doctors->isEmpty()) {
  echo "❌ No se encontraron doctores en el sistema.\n";
  exit(1);
}

$daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
$created = 0;
$skipped = 0;

foreach ($doctors as $doctor) {
  echo "👨‍⚕️ {$doctor->name}\n";

  foreach ($daysOfWeek as $day) {
    // Check if schedule already exists
    $exists = DoctorSchedule::where('doctor_id', $doctor->id)
      ->where('day_of_week', $day)
      ->exists();

    if ($exists) {
      $skipped++;
      continue;
    }

    // Create default schedule: 8:00 - 21:00, 30 min slots
    DoctorSchedule::create([
      'doctor_id' => $doctor->id,
      'day_of_week' => $day,
      'start_time' => '08:00:00',
      'end_time' => '21:00:00',
      'slot_duration' => 30,
      'is_active' => true,
    ]);

    $created++;
  }

  echo "   ✅ Horarios configurados: Lunes a Viernes 8:00-21:00 (30 min)\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Proceso completado\n";
echo "   Horarios creados: {$created}\n";
echo "   Horarios existentes: {$skipped}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
