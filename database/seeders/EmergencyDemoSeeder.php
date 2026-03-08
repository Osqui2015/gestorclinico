<?php

namespace Database\Seeders;

use App\Models\EmergencyAdmission;
use App\Models\EmergencyEvolution;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EmergencyDemoSeeder extends Seeder
{
  public function run(): void
  {
    $this->command->info('Creando datos de demostración para emergencies...');

    $patients = Patient::take(5)->get();
    $emergency = User::where('role', 'emergency')->first();
    $doctors = User::where('role', 'doctor')->limit(3)->get();

    if (!$emergency || $patients->count() < 3) {
      $this->command->warn('⚠️  No hay suficientes datos base');
      return;
    }

    // Admisión 1: Triage nivel 2 (Emergencia)
    $admission1 = EmergencyAdmission::create([
      'patient_id' => $patients[0]->id,
      'attending_doctor_id' => $doctors[0]->id,
      'admission_time' => now()->subHour(),
      'triage_time' => now()->subMinutes(55),
      'triage_level' => '2',
      'chief_complaint' => 'Dolor abdominal agudo y vómitos',
      'triage_notes' => 'Paciente con signos de deshidratación',
      'status' => 'in_care',
      'systolic_pressure' => 145,
      'diastolic_pressure' => 95,
      'heart_rate' => 102,
      'respiratory_rate' => 20,
      'temperature' => 38.5,
      'oxygen_saturation' => 96,
      'preliminary_diagnosis' => 'Posible appendicitis',
    ]);

    // Evolución 1
    EmergencyEvolution::create([
      'emergency_admission_id' => $admission1->id,
      'recorded_by' => $doctors[0]->id,
      'recorded_at' => now()->subMinutes(30),
      'systolic_pressure' => 142,
      'diastolic_pressure' => 92,
      'heart_rate' => 98,
      'respiratory_rate' => 20,
      'temperature' => 38.2,
      'oxygen_saturation' => 96,
      'clinical_notes' => 'Paciente con mejoría, dolor reducido',
      'treatment_notes' => 'Analgesia EV, hidratación parenteral',
      'medications_given' => 'Ketorolac 30mg IV, Plasmalyte 1L',
    ]);

    // Admisión 2: Triage nivel 3 (Urgencia)
    $admission2 = EmergencyAdmission::create([
      'patient_id' => $patients[1]->id,
      'attending_doctor_id' => $doctors[1]->id,
      'admission_time' => now()->subMinutes(45),
      'triage_time' => now()->subMinutes(42),
      'triage_level' => '3',
      'chief_complaint' => 'Herida lacerante en mano derecha',
      'triage_notes' => 'Herida con sangrado controlado',
      'status' => 'waiting',
      'systolic_pressure' => 120,
      'diastolic_pressure' => 80,
      'heart_rate' => 78,
      'respiratory_rate' => 16,
      'temperature' => 36.8,
      'oxygen_saturation' => 98,
      'preliminary_diagnosis' => 'Herida lacerante mano derecha 3cm',
    ]);

    // Admisión 3: Triage nivel 1 (Resucitación)
    $admission3 = EmergencyAdmission::create([
      'patient_id' => $patients[2]->id,
      'attending_doctor_id' => $doctors[0]->id,
      'admission_time' => now()->subMinutes(15),
      'triage_time' => now()->subMinutes(14),
      'triage_level' => '1',
      'chief_complaint' => 'Dificultad respiratoria severa',
      'triage_notes' => 'Paciente cianótico, requiere O2 urgente',
      'status' => 'in_care',
      'systolic_pressure' => 102,
      'diastolic_pressure' => 65,
      'heart_rate' => 125,
      'respiratory_rate' => 35,
      'temperature' => 36.2,
      'oxygen_saturation' => 78,
      'consciousness_level' => 'GCS 14',
      'preliminary_diagnosis' => 'Asma aguda severa',
    ]);

    // Evolución 2
    EmergencyEvolution::create([
      'emergency_admission_id' => $admission3->id,
      'recorded_by' => $doctors[0]->id,
      'recorded_at' => now(),
      'systolic_pressure' => 115,
      'diastolic_pressure' => 75,
      'heart_rate' => 102,
      'respiratory_rate' => 24,
      'temperature' => 36.5,
      'oxygen_saturation' => 94,
      'clinical_notes' => 'Paciente con buena respuesta a broncodilatadores',
      'treatment_notes' => 'Salbutamol nebulizado, metilprednisolona IV',
      'medications_given' => 'Albuterol 2.5mg neb, Metilprednisolona 125mg IV',
    ]);

    // Admisión alta (ya dada de alta)
    $admission4 = EmergencyAdmission::create([
      'patient_id' => $patients[3]->id,
      'admission_time' => now()->subDays(1)->setTime(14, 30),
      'triage_time' => now()->subDays(1)->setTime(14, 35),
      'discharged_at' => now()->subDays(1)->setTime(16, 0),
      'triage_level' => '4',
      'chief_complaint' => 'Mareos y debilidad',
      'status' => 'discharged',
      'systolic_pressure' => 110,
      'diastolic_pressure' => 70,
      'heart_rate' => 72,
      'respiratory_rate' => 18,
      'temperature' => 36.9,
      'oxygen_saturation' => 98,
      'discharge_diagnosis' => 'Hipotensión ortostática',
      'discharge_instructions' => 'Reposo, hidratación oral, control de PA',
    ]);

    $this->command->info('✅ Datos de demostración de emergencias creados exitosamente.');
    $this->command->info('Resumen:');
    $this->command->info('  - Admisiones en espera: ' . EmergencyAdmission::where('status', 'waiting')->count());
    $this->command->info('  - Admisiones en atención: ' . EmergencyAdmission::where('status', 'in_care')->count());
    $this->command->info('  - Admisiones dadas de alta: ' . EmergencyAdmission::where('status', 'discharged')->count());
  }
}
