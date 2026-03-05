<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
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

        $notes = [
            'Primera vez consultando.',
            'Seguimiento de tratamiento.',
            'Control periódico.',
            'Empeoramiento de síntomas.',
            'Mejora notable.',
            'Requiere pruebas adicionales.',
            'Derivación externa.',
            'Consulta de urgencia.',
        ];

        return [
            'reason' => $this->faker->randomElement($reasons),
            'notes' => $this->faker->optional(0.7)->randomElement($notes),
            'status' => $this->faker->randomElement(['pending', 'called', 'attending', 'completed', 'cancelled']),
        ];
    }
}
