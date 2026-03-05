<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@clinica.com',
            'role' => 'admin',
        ]);

        // Create doctors
        $doctor1 = User::factory()->create([
            'name' => 'Dr. Juan García',
            'email' => 'juan.garcia@clinica.com',
            'role' => 'doctor',
            'specialty' => 'Cardiología',
        ]);

        $doctor2 = User::factory()->create([
            'name' => 'Dra. María López',
            'email' => 'maria.lopez@clinica.com',
            'role' => 'doctor',
            'specialty' => 'Pediatría',
        ]);

        $doctor3 = User::factory()->create([
            'name' => 'Dr. Carlos Martínez',
            'email' => 'carlos.martinez@clinica.com',
            'role' => 'doctor',
            'specialty' => 'Neurología',
        ]);

        // Create a secretary user for testing
        $secretary = User::factory()->create([
            'name' => 'Secretaria Principal',
            'email' => 'secretaria@clinica.com',
            'role' => 'secretary',
        ]);

        // Create 15 patients
        $patients = Patient::factory(15)->create();

        // Create appointments for today and upcoming days
        $usedSlots = []; // Track used time slots per doctor

        foreach ($patients as $patient) {
            // Random number of appointments per patient (1-3)
            $appointmentCount = rand(1, 3);

            for ($i = 0; $i < $appointmentCount; $i++) {
                $randomDoctor = collect([$doctor1, $doctor2, $doctor3])->random();

                // Create appointments spread across today and next 30 days
                $daysOffset = rand(0, 30);

                // Generate unique time slot
                $maxAttempts = 10;
                $attempt = 0;
                do {
                    $scheduledTime = now()->addDays($daysOffset)
                        ->setHour(rand(9, 17))
                        ->setMinute(rand(0, 3) * 15)
                        ->setSecond(0);
                    $slotKey = $randomDoctor->id . '-' . $scheduledTime->format('Y-m-d H:i:s');
                    $attempt++;
                } while (isset($usedSlots[$slotKey]) && $attempt < $maxAttempts);

                if ($attempt >= $maxAttempts) {
                    continue; // Skip this appointment if can't find unique slot
                }

                $usedSlots[$slotKey] = true;

                $appointment = Appointment::factory()->create([
                    'doctor_id' => $randomDoctor->id,
                    'patient_id' => $patient->id,
                    'scheduled_at' => $scheduledTime,
                    'status' => $daysOffset === 0 ? 'pending' : 'called', // Today's are pending, future called
                ]);

                // Create medical records for past appointments
                if ($daysOffset < 0) {
                    MedicalRecord::factory()->create([
                        'patient_id' => $patient->id,
                        'appointment_id' => $appointment->id,
                        'doctor_id' => $randomDoctor->id,
                    ]);
                }
            }

            // Add some medical history (past medical records without appointments)
            $pastRecordsCount = rand(1, 4);
            for ($i = 0; $i < $pastRecordsCount; $i++) {
                $randomDoctor = collect([$doctor1, $doctor2, $doctor3])->random();
                MedicalRecord::factory()->create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $randomDoctor->id,
                    'appointment_id' => null, // No associated appointment
                    'created_at' => now()->subDays(rand(1, 180)), // Past records
                    'updated_at' => now()->subDays(rand(1, 180)),
                ]);
            }
        }
    }
}
