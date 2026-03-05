<?php
// Script para crear datos de prueba

require 'vendor/autoload.php';

// Crear la aplicación Laravel
$app = require_once 'bootstrap/app.php';

// Obtener la aplicación
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// Ejecutar el artisan dentro del contexto de la app
$kernel->bootstrap();

use App\Models\User;
use App\Models\Patient;
use App\Models\Prescription;

try {
  // Crear doctor
  $doctor = User::create([
    'name' => 'Dr. Juan García',
    'email' => 'doctor@test.local',
    'password' => bcrypt('password123'),
    'role' => 'doctor',
    'matricula_provincial' => 'TUCUMAN-12345',
    'matricula_nacional' => 'NACIONAL-67890',
    'especialidad_medica' => 'Medicina General',
    'matricula_validada' => true,
    'habilitado_renapdis' => true
  ]);

  echo "✓ Doctor creado: Dr. Juan García (ID: {$doctor->id})\n";

  // Crear paciente
  $patient = Patient::create([
    'user_id' => $doctor->id,
    'nombre' => 'Carlos López',
    'apellido' => 'Rodríguez',
    'dni' => '12345678',
    'fecha_nacimiento' => '1980-05-15',
    'telefono' => '+54 381 555-1234',
    'email' => 'patient@test.local',
    'obra_social' => 'PAMI',
    'numero_afiliado' => 'AFF-123456789',
    'activo' => true
  ]);

  echo "✓ Paciente creado: Carlos López Rodríguez (ID: {$patient->id})\n";

  // Crear receta
  $cuir = 'CUIR-' . substr(md5(time()), 0, 16);
  $prescription = Prescription::create([
    'patient_id' => $patient->id,
    'doctor_id' => $doctor->id,
    'descripcion' => 'Receta de prueba ReNaPDiS',
    'medicamentos' => json_encode([
      [
        'nombre' => 'Ibupirac 400mg',
        'dosis' => '400 mg',
        'frecuencia' => 'Cada 8 horas',
        'duracion' => '7 días',
        'via_administracion' => 'Oral',
        'cantidad' => '21'
      ]
    ]),
    'diagnostico' => 'Infección respiratoria',
    'observaciones' => 'Completar los 7 días',
    'cuir' => $cuir,
    'matricula_profesional' => $doctor->matricula_nacional,
    'medicamentos_genericos' => true,
    'cie10_codigo' => 'J06.9',
    'firma_electronica_hash' => hash('sha256', 'test-signature-' . time()),
    'qr_code_path' => '/storage/qr-codes/test-' . time() . '.png',
    'estado_dispensacion' => 'pendiente',
    'fecha_vencimiento' => now()->addDays(30),
    'obra_social' => $patient->obra_social,
    'numero_afiliado' => $patient->numero_afiliado,
    'habilitado_renapdis' => true,
    'validado_refeps' => true,
    'validado_renaper' => true
  ]);

  echo "✓ Receta creada: {$cuir} (ID: {$prescription->id})\n";
  echo "\n✅ Datos de prueba insertados exitosamente\n";
} catch (\Exception $e) {
  echo "❌ Error: " . $e->getMessage() . "\n";
  echo $e->getTraceAsString();
}
