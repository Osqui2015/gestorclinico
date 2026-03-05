<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\HealthInsurance;
use App\Models\DoctorSchedule;
use App\Models\DoctorException;
use App\Models\Appointment;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CriticalPrioritySeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // 1. Crear usuarios del sistema
    $admin = User::create([
      'name' => 'Administrador Sistema',
      'email' => 'admin@clinica.com',
      'password' => Hash::make('password'),
      'role' => 'admin',
      'specialty' => null,
    ]);

    $secretary = User::create([
      'name' => 'María González',
      'email' => 'secretaria@clinica.com',
      'password' => Hash::make('password'),
      'role' => 'secretary',
      'specialty' => null,
    ]);

    $doctor1 = User::create([
      'name' => 'Dr. Carlos Pérez',
      'email' => 'carlosperez@clinica.com',
      'password' => Hash::make('password'),
      'role' => 'doctor',
      'specialty' => 'Cardiología',
    ]);

    $doctor2 = User::create([
      'name' => 'Dra. Ana Martínez',
      'email' => 'anamartinez@clinica.com',
      'password' => Hash::make('password'),
      'role' => 'doctor',
      'specialty' => 'Pediatría',
    ]);

    $doctor3 = User::create([
      'name' => 'Dr. Jorge López',
      'email' => 'jorgelopez@clinica.com',
      'password' => Hash::make('password'),
      'role' => 'doctor',
      'specialty' => 'Medicina General',
    ]);

    // 2. Crear horarios para los doctores

    // Dr. Carlos Pérez - Cardiólogo (Lunes a Viernes, mañana y tarde)
    $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    foreach ($daysOfWeek as $day) {
      DoctorSchedule::create([
        'doctor_id' => $doctor1->id,
        'day_of_week' => $day,
        'start_time' => '08:00',
        'end_time' => '12:00',
        'slot_duration' => 30,
        'is_active' => true,
      ]);
      DoctorSchedule::create([
        'doctor_id' => $doctor1->id,
        'day_of_week' => $day,
        'start_time' => '14:00',
        'end_time' => '18:00',
        'slot_duration' => 30,
        'is_active' => true,
      ]);
    }

    // Dra. Ana Martínez - Pediatra (Lunes, Miércoles, Viernes)
    foreach (['monday', 'wednesday', 'friday'] as $day) {
      DoctorSchedule::create([
        'doctor_id' => $doctor2->id,
        'day_of_week' => $day,
        'start_time' => '09:00',
        'end_time' => '13:00',
        'slot_duration' => 20,
        'is_active' => true,
      ]);
      DoctorSchedule::create([
        'doctor_id' => $doctor2->id,
        'day_of_week' => $day,
        'start_time' => '15:00',
        'end_time' => '19:00',
        'slot_duration' => 20,
        'is_active' => true,
      ]);
    }

    // Dr. Jorge López - Medicina General (Todos los días)
    $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    foreach ($allDays as $day) {
      DoctorSchedule::create([
        'doctor_id' => $doctor3->id,
        'day_of_week' => $day,
        'start_time' => '08:00',
        'end_time' => '14:00',
        'slot_duration' => 15,
        'is_active' => true,
      ]);
    }

    // 3. Crear algunas excepciones (vacaciones)
    DoctorException::create([
      'doctor_id' => $doctor1->id,
      'exception_date' => Carbon::now()->addDays(10),
      'type' => 'conference',
      'reason' => 'Congreso de Cardiología en Buenos Aires',
      'is_all_day' => true,
    ]);

    DoctorException::create([
      'doctor_id' => $doctor2->id,
      'exception_date' => Carbon::now()->addDays(5),
      'type' => 'vacation',
      'reason' => 'Vacaciones personales',
      'is_all_day' => true,
    ]);

    // 4. Crear obras sociales
    $osde = HealthInsurance::create([
      'name' => 'OSDE',
      'code' => 'OSDE',
      'phone' => '0810-555-6733',
      'email' => 'contacto@osde.com.ar',
      'copay_amount' => 500.00,
      'copay_percentage' => 0,
      'requires_authorization' => true,
      'is_active' => true,
    ]);

    $swissMedical = HealthInsurance::create([
      'name' => 'Swiss Medical',
      'code' => 'SWISS',
      'phone' => '0810-333-7947',
      'email' => 'info@swissmedical.com.ar',
      'copay_amount' => 0,
      'copay_percentage' => 20,
      'requires_authorization' => false,
      'is_active' => true,
    ]);

    $particular = HealthInsurance::create([
      'name' => 'Particular (Sin Obra Social)',
      'code' => 'PARTICULAR',
      'phone' => null,
      'email' => null,
      'copay_amount' => 0,
      'copay_percentage' => 0,
      'requires_authorization' => false,
      'is_active' => true,
    ]);

    // 5. Crear pacientes de prueba
    $patient1 = Patient::create([
      'first_name' => 'Juan',
      'last_name' => 'Rodríguez',
      'dni' => '20456789',
      'birth_date' => '1975-05-15',
      'phone' => '11-5555-1234',
      'email' => 'juan.rodriguez@email.com',
      'address' => 'Av. Corrientes 1234',
      'city' => 'Buenos Aires',
      'zip_code' => '1043',
      'gender' => 'male',
      'emergency_contact_name' => 'María Rodríguez',
      'emergency_contact_phone' => '11-5555-5678',
      'allergies' => 'Penicilina, Polen',
      'notes' => 'Paciente con hipertensión controlada',
    ]);

    $patient2 = Patient::create([
      'first_name' => 'Laura',
      'last_name' => 'Fernández',
      'dni' => '35678901',
      'birth_date' => '1990-08-22',
      'phone' => '11-6666-2345',
      'email' => 'laura.fernandez@email.com',
      'address' => 'Calle Falsa 123',
      'city' => 'Buenos Aires',
      'zip_code' => '1414',
      'gender' => 'female',
      'emergency_contact_name' => 'Pedro Fernández',
      'emergency_contact_phone' => '11-6666-6789',
      'allergies' => null,
      'notes' => null,
    ]);

    $patient3 = Patient::create([
      'first_name' => 'Sofía',
      'last_name' => 'García',
      'dni' => '48123456',
      'birth_date' => '2018-03-10',
      'phone' => '11-7777-3456',
      'email' => null,
      'address' => 'San Martín 456',
      'city' => 'Buenos Aires',
      'zip_code' => '1640',
      'gender' => 'female',
      'emergency_contact_name' => 'Clara García (Madre)',
      'emergency_contact_phone' => '11-7777-7890',
      'allergies' => 'Maní, Huevo',
      'notes' => 'Paciente pediátrico - Traer carnet de vacunación',
    ]);

    // Asociar pacientes con obras sociales
    $patient1->healthInsurances()->attach($osde->id, [
      'member_number' => 'OSDE-123456',
      'valid_from' => Carbon::now()->subYear(),
      'valid_until' => Carbon::now()->addYear(),
      'is_primary' => true,
    ]);

    $patient2->healthInsurances()->attach($swissMedical->id, [
      'member_number' => 'SWISS-789012',
      'valid_from' => Carbon::now()->subMonths(6),
      'valid_until' => Carbon::now()->addMonths(18),
      'is_primary' => true,
    ]);

    $patient3->healthInsurances()->attach($particular->id, [
      'member_number' => null,
      'valid_from' => null,
      'valid_until' => null,
      'is_primary' => true,
    ]);

    // 6. Crear algunos turnos de ejemplo (hoy y próximos días)

    // Turnos para hoy
    $today = Carbon::today();

    Appointment::create([
      'doctor_id' => $doctor1->id,
      'patient_id' => $patient1->id,
      'scheduled_at' => $today->copy()->setTime(9, 0),
      'duration' => 30,
      'status' => 'pending',
      'reason' => 'Control cardiológico',
      'notes' => 'Paciente con antecedentes de hipertensión',
      'is_walk_in' => false,
      'confirmed' => true,
      'confirmed_at' => $today->copy()->subDay(),
      'checked_in_at' => $today->copy()->setTime(8, 55),
    ]);

    Appointment::create([
      'doctor_id' => $doctor2->id,
      'patient_id' => $patient3->id,
      'scheduled_at' => $today->copy()->setTime(10, 0),
      'duration' => 20,
      'status' => 'completed',
      'reason' => 'Control pediátrico',
      'notes' => 'Vacunación al día',
      'is_walk_in' => false,
      'confirmed' => true,
      'confirmed_at' => $today->copy()->subDays(2),
      'checked_in_at' => $today->copy()->setTime(9, 50),
    ]);

    Appointment::create([
      'doctor_id' => $doctor3->id,
      'patient_id' => $patient2->id,
      'scheduled_at' => $today->copy()->setTime(11, 30),
      'duration' => 15,
      'status' => 'pending',
      'reason' => 'Consulta general',
      'notes' => null,
      'is_walk_in' => false,
      'confirmed' => false,
      'confirmed_at' => null,
      'checked_in_at' => null,
    ]);

    // Turno de mañana
    Appointment::create([
      'doctor_id' => $doctor1->id,
      'patient_id' => $patient2->id,
      'scheduled_at' => $today->copy()->addDay()->setTime(14, 0),
      'duration' => 30,
      'status' => 'pending',
      'reason' => 'Primera consulta',
      'notes' => null,
      'is_walk_in' => false,
      'confirmed' => false,
      'confirmed_at' => null,
      'checked_in_at' => null,
    ]);

    $this->command->info('✅ Datos de prueba creados exitosamente!');
    $this->command->info('');
    $this->command->info('👤 Usuarios creados:');
    $this->command->info('   Admin: admin@clinica.com / password');
    $this->command->info('   Secretaria: secretaria@clinica.com / password');
    $this->command->info('   Dr. Carlos Pérez: carlosperez@clinica.com / password');
    $this->command->info('   Dra. Ana Martínez: anamartinez@clinica.com / password');
    $this->command->info('   Dr. Jorge López: jorgelopez@clinica.com / password');
    $this->command->info('');
    $this->command->info('🏥 3 Obras Sociales creadas');
    $this->command->info('👥 3 Pacientes de prueba creados');
    $this->command->info('📅 4 Turnos de ejemplo creados (incluyendo hoy)');
    $this->command->info('⏰ Horarios configurados para todos los doctores');
  }
}
