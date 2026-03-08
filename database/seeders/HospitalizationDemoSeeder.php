<?php

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\BedCleaningLog;
use App\Models\Hospitalization;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder de datos de demostración para el módulo de internación.
 *
 * Crea escenarios realistas de hospitalización para testing y demostración:
 * - Hospitalizaciones activas (diferentes tipos)
 * - Hospitalizaciones completadas con alta
 * - Camas en diferentes estados (ocupadas, pendientes de limpieza)
 * - Registros de limpieza de camas
 *
 * IMPORTANTE: Este seeder requiere que existan pacientes, médicos y camas
 * en la base de datos. Ejecutar después de HospitalizationSetupSeeder.
 */
class HospitalizationDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existen los datos requeridos
        if (Patient::count() === 0) {
            $this->command->warn('⚠️  No hay pacientes en la base de datos. Ejecute primero el seeder de pacientes.');
            return;
        }

        if (User::where('role', 'doctor')->count() === 0) {
            $this->command->warn('⚠️  No hay médicos en la base de datos.');
            return;
        }

        if (Bed::count() === 0) {
            $this->command->warn('⚠️  No hay camas en la base de datos. Ejecute primero HospitalizationSetupSeeder.');
            return;
        }

        $this->command->info('Creando datos de demostración para internación...');

        // Obtener datos existentes
        $patients = Patient::take(5)->get();
        $doctors = User::where('role', 'doctor')->get();
        $beds = Bed::all();

        if ($patients->count() < 3 || $doctors->count() < 2 || $beds->count() < 4) {
            $this->command->warn('⚠️  No hay suficientes datos base (mínimo: 3 pacientes, 2 médicos, 4 camas).');
            return;
        }

        // Escenario 1: Hospitalización activa estándar (internado hoy)
        $bed1 = $beds->where('status', Bed::STATUS_AVAILABLE)->first();
        if ($bed1) {
            $hospitalization1 = Hospitalization::updateOrCreate(
                [
                    'patient_id' => $patients[0]->id,
                    'bed_id' => $bed1->id,
                ],
                [
                    'admission_date' => Carbon::now()->subHours(6),
                    'admission_type' => 'scheduled',
                    'admission_reason' => 'Cirugía programada de apendicectomía. Postoperatorio inmediato.',
                    'doctor_id' => $doctors[0]->id,
                    'expected_discharge_date' => Carbon::now()->addDays(3),
                    'diagnosis' => 'Apendicitis aguda',
                    'treatment' => 'Apendicectomía laparoscópica. Analgesia EV. Hidratación parenteral.',
                    'daily_observations' => 'Paciente estable. Signos vitales normales. Dolor controlado con analgesia.',
                    'status' => 'active',
                ]
            );

            // Marcar cama como ocupada
            $bed1->update(['status' => Bed::STATUS_OCCUPIED]);

            $this->command->info("✓ Hospitalización activa creada: {$patients[0]->full_name} en {$bed1->room->code} Cama {$bed1->bed_number}");
        }

        // Escenario 2: Hospitalización activa en UCI (admitido hace 2 días)
        $uciBed = $beds->where('bed_type', Bed::TYPE_INTENSIVE_CARE)
            ->where('status', Bed::STATUS_AVAILABLE)
            ->first();

        if ($uciBed && $patients->count() > 1) {
            $hospitalization2 = Hospitalization::updateOrCreate(
                [
                    'patient_id' => $patients[1]->id,
                    'bed_id' => $uciBed->id,
                ],
                [
                    'admission_date' => Carbon::now()->subDays(2)->setTime(14, 30),
                    'admission_type' => 'emergency',
                    'admission_reason' => 'Insuficiencia respiratoria aguda. Requiere monitoreo intensivo.',
                    'doctor_id' => $doctors[1]->id ?? $doctors[0]->id,
                    'expected_discharge_date' => null, // No determinado en UCI
                    'diagnosis' => 'Insuficiencia respiratoria aguda, probable SDRA',
                    'treatment' => 'Ventilación mecánica invasiva. Sedoanalgesia. ATB empírico.',
                    'daily_observations' => 'Paciente en ventilación mecánica. Evolución favorable. Parámetros hemodinámicos estables.',
                    'status' => 'active',
                ]
            );

            // Marcar cama UCI como ocupada
            $uciBed->update(['status' => Bed::STATUS_OCCUPIED]);

            $this->command->info("✓ Hospitalización UCI creada: {$patients[1]->full_name} en {$uciBed->room->code} Cama {$uciBed->bed_number}");
        }

        // Escenario 3: Hospitalización completada (alta ayer, cama pendiente de limpieza)
        $bed3 = $beds->where('status', Bed::STATUS_AVAILABLE)
            ->where('bed_type', Bed::TYPE_STANDARD)
            ->skip(1)
            ->first();

        if ($bed3 && $patients->count() > 2) {
            $admissionDate = Carbon::now()->subDays(5);
            $dischargeDate = Carbon::yesterday()->setTime(11, 45);

            $hospitalization3 = Hospitalization::updateOrCreate(
                [
                    'patient_id' => $patients[2]->id,
                    'bed_id' => $bed3->id,
                ],
                [
                    'admission_date' => $admissionDate,
                    'admission_type' => 'scheduled',
                    'admission_reason' => 'Neumonía adquirida en la comunidad. Antibioticoterapia endovenosa.',
                    'doctor_id' => $doctors[0]->id,
                    'expected_discharge_date' => $dischargeDate->copy()->subDay(),
                    'actual_discharge_date' => $dischargeDate,
                    'diagnosis' => 'Neumonía adquirida en la comunidad',
                    'treatment' => 'Ceftriaxona + Azitromicina EV. Hidratación. Oxigenoterapia.',
                    'discharge_notes' => 'Alta médica. Mejoría clínica. Continuar con antibióticos vía oral por 7 días.',
                    'daily_observations' => 'Paciente con evolución favorable. Afebril hace 48hs. Laboratorio normalizado.',
                    'status' => 'discharged',
                ]
            );

            // Marcar cama como pendiente de limpieza
            $bed3->update(['status' => Bed::STATUS_PENDING_CLEANING]);

            $this->command->info("✓ Hospitalización completada: {$patients[2]->full_name} dado de alta (cama pendiente limpieza)");
        }

        // Escenario 4: Cama limpia con registro de limpieza completado (limpiada esta mañana)
        $bed4 = $beds->where('status', Bed::STATUS_AVAILABLE)
            ->where('bed_type', Bed::TYPE_STANDARD)
            ->skip(2)
            ->first();

        if ($bed4 && $patients->count() > 3) {
            // Crear una hospitalización antigua completada
            $oldAdmission = Carbon::now()->subDays(8);
            $oldDischarge = Carbon::now()->subDays(1)->setTime(9, 0);

            Hospitalization::updateOrCreate(
                [
                    'patient_id' => $patients[3]->id,
                    'bed_id' => $bed4->id,
                ],
                [
                    'admission_date' => $oldAdmission,
                    'admission_type' => 'transfer',
                    'admission_reason' => 'Traslado desde otro centro. Control postoperatorio.',
                    'doctor_id' => $doctors[1]->id ?? $doctors[0]->id,
                    'actual_discharge_date' => $oldDischarge,
                    'diagnosis' => 'Postoperatorio de colecistectomía',
                    'treatment' => 'Analgesia. Control de herida operatoria.',
                    'discharge_notes' => 'Alta por mejoría. Control ambulatorio.',
                    'status' => 'discharged',
                ]
            );

            // Crear registro de limpieza completado
            $cleaningStart = Carbon::now()->setTime(8, 0);
            $cleaningEnd = Carbon::now()->setTime(8, 45);

            $nurseUser = User::where('role', 'nurse')->first();

            if ($nurseUser) {
                BedCleaningLog::updateOrCreate(
                    [
                        'bed_id' => $bed4->id,
                        'cleaned_by' => $nurseUser->id,
                    ],
                    [
                        'cleaning_started_at' => $cleaningStart,
                        'cleaning_completed_at' => $cleaningEnd,
                        'observations' => 'Limpieza profunda completada. Desinfección terminal. Ropa de cama renovada.',
                    ]
                );

                $this->command->info("✓ Registro de limpieza creado: {$bed4->room->code} Cama {$bed4->bed_number} limpiada por {$nurseUser->name}");
            }
        }

        // Escenario 5: Hospitalización activa con fecha de alta estimada próxima
        $bed5 = $beds->where('status', Bed::STATUS_AVAILABLE)
            ->where('bed_type', Bed::TYPE_STANDARD)
            ->skip(3)
            ->first();

        if ($bed5 && $patients->count() > 4) {
            Hospitalization::updateOrCreate(
                [
                    'patient_id' => $patients[4]->id,
                    'bed_id' => $bed5->id,
                ],
                [
                    'admission_date' => Carbon::now()->subDays(4)->setTime(16, 20),
                    'admission_type' => 'emergency',
                    'admission_reason' => 'Celulitis en miembro inferior derecho. Antibioticoterapia EV.',
                    'doctor_id' => $doctors[0]->id,
                    'expected_discharge_date' => Carbon::tomorrow(),
                    'diagnosis' => 'Celulitis miembro inferior derecho',
                    'treatment' => 'Cefazolina EV. Analgesia. Elevación de miembro.',
                    'daily_observations' => 'Mejoría evidente. Disminución del eritema y dolor. Planear alta mañana.',
                    'status' => 'active',
                ]
            );

            $bed5->update(['status' => Bed::STATUS_OCCUPIED]);

            $this->command->info("✓ Hospitalización con alta próxima: {$patients[4]->full_name} (alta prevista mañana)");
        }

        $this->command->info('✅ Datos de demostración de internación creados exitosamente.');
        $this->command->info('');
        $this->command->info('Resumen:');
        $this->command->info('  - Hospitalizaciones activas: ' . Hospitalization::where('status', 'active')->count());
        $this->command->info('  - Hospitalizaciones completadas: ' . Hospitalization::whereIn('status', ['discharged', 'transferred'])->count());
        $this->command->info('  - Camas ocupadas: ' . Bed::where('status', Bed::STATUS_OCCUPIED)->count());
        $this->command->info('  - Camas pendientes limpieza: ' . Bed::where('status', Bed::STATUS_PENDING_CLEANING)->count());
        $this->command->info('  - Camas disponibles: ' . Bed::where('status', Bed::STATUS_AVAILABLE)->count());
        $this->command->info('  - Registros de limpieza: ' . BedCleaningLog::count());
    }
}
