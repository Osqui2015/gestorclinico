<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Appointment;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Crear un médico de prueba
    $doctor = User::factory()->create([
      'role' => 'doctor',
      'name' => 'Dr. Juan García',
      'email' => 'doctor@test.local',
      'matricula_provincial' => 'TUCUMAN-12345',
      'matricula_nacional' => 'NACIONAL-67890',
      'especialidad_medica' => 'Medicina General',
      'matricula_validada' => true,
      'habilitado_renapdis' => true
    ]);

    // Crear un paciente de prueba
    $patient = Patient::factory()->create([
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

    // Crear una cita
    $appointment = Appointment::factory()->create([
      'patient_id' => $patient->id,
      'doctor_id' => $doctor->id,
      'fecha_hora' => now()->subDay(),
      'estado' => 'completada'
    ]);

    // Crear una receta digital con todos los campos ReNaPDiS
    $prescription = Prescription::create([
      'patient_id' => $patient->id,
      'doctor_id' => $doctor->id,
      'appointment_id' => $appointment->id,
      'descripcion' => 'Receta de prueba para validación de ReNaPDiS',
      'medicamentos' => json_encode([
        [
          'nombre' => 'Ibupirac 400mg',
          'dosis' => '400 mg',
          'frecuencia' => 'Cada 8 horas',
          'duracion' => '7 días',
          'via_administracion' => 'Oral',
          'cantidad' => '21'
        ],
        [
          'nombre' => 'Amoxicilina 500mg',
          'dosis' => '500 mg',
          'frecuencia' => 'Cada 12 horas',
          'duracion' => '10 días',
          'via_administracion' => 'Oral',
          'cantidad' => '20'
        ]
      ]),
      'diagnostico' => 'Infección respiratoria aguda leve',
      'observaciones' => 'Tomar con alimentos. Completar los 10 días de tratamiento.',
      // Campos ReNaPDiS
      'cuir' => 'CUIR-' . substr(md5(time()), 0, 16),
      'matricula_profesional' => $doctor->matricula_nacional,
      'medicamentos_genericos' => true,
      'cie10_codigo' => 'J06.9',
      'firma_electronica_hash' => hash('sha256', 'test-signature-' . time()),
      'qr_code_path' => '/storage/qr-codes/test-' . now()->getTimestamp() . '.png',
      'estado_dispensacion' => 'pendiente',
      'fecha_vencimiento' => now()->addDays(30),
      'obra_social' => $patient->obra_social,
      'numero_afiliado' => $patient->numero_afiliado,
      'habilitado_renapdis' => true,
      'validado_refeps' => true,
      'validado_renaper' => true
    ]);

    $this->command->info('Datos de prueba creados exitosamente:');
    $this->command->info('Médico: ' . $doctor->name . ' (ID: ' . $doctor->id . ')');
    $this->command->info('Paciente: ' . $patient->nombre . ' ' . $patient->apellido . ' (ID: ' . $patient->id . ')');
    $this->command->info('Receta: CUIR ' . $prescription->cuir . ' (ID: ' . $prescription->id . ')');
  }
}
