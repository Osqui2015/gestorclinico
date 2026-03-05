<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicalRecord>
 */
class MedicalRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reasons = [
            'Chequeo general',
            'Dolor de cabeza',
            'Dolor de espalda',
            'Gripe',
            'Tos persistente',
            'Control de presión arterial',
            'Diabetes check-up',
            'Ansiedad',
            'Insomnio',
            'Problemas digestivos',
        ];

        $diagnoses = [
            'Paciente estable',
            'Migraña tensional',
            'Lumbalgia',
            'Gripe común',
            'Bronquitis leve',
            'Hipertensión controlada',
            'Diabetes tipo 2 compensada',
            'Trastorno de ansiedad generalizada',
            'Insomnio primario',
            'Gastritis',
        ];

        $treatments = [
            'Reposo y analgésicos',
            'Antiinflamatorios',
            'Antitusígenos',
            'Antihipertensivos',
            'Ajuste de insulina',
            'Psicoterapia y relajación',
            'Higiene del sueño',
            'Dieta blanda',
            'Antibióticos si es necesario',
            'Seguimiento en 1 semana',
        ];

        $privateNotes = [
            'Paciente muy preocupado, requiere apoyo emocional adicional.',
            'Estado de salud mejorado respecto a la consulta anterior.',
            'Considerar derivación a especialista.',
            'Paciente cumple adecuadamente con el tratamiento.',
            'Requiere controles más frecuentes.',
            'Excelente evolución clínica.',
            'Recomendado cambio de medicación.',
            'Paciente comprende bien las instrucciones.',
        ];

        return [
            'reason' => $this->faker->randomElement($reasons),
            'diagnosis' => $this->faker->randomElement($diagnoses),
            'treatment' => $this->faker->randomElement($treatments),
            'private_notes' => $this->faker->randomElement($privateNotes),
            'attachments' => null,
            'appointment_id' => null,
        ];
    }
}
