<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'doctor_id' => 'required|exists:users,id|exists:users,id,role,doctor',
            'patient_id' => 'required|exists:patients,id',
            'health_insurance_id' => 'nullable|exists:health_insurances,id',
            'coseguro' => 'nullable|numeric|min:0|max:999999.99',
            'scheduled_at' => [
                'required',
                'date_format:Y-m-d H:i',
                'after:now',
                // Validación: no puede haber 2 turnos al mismo tiempo para el mismo médico (ignorando la cita actual)
                Rule::unique('appointments', 'scheduled_at')
                    ->where('doctor_id', $this->doctor_id)
                    ->ignore($this->appointment->id),
            ],
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.exists' => 'El médico seleccionado no existe o no tiene rol de doctor.',
            'patient_id.exists' => 'El paciente seleccionado no existe.',
            'health_insurance_id.exists' => 'La obra social seleccionada no existe.',
            'coseguro.numeric' => 'El coseguro debe ser un número válido.',
            'coseguro.min' => 'El coseguro no puede ser negativo.',
            'coseguro.max' => 'El coseguro no puede superar $999,999.99.',
            'scheduled_at.date_format' => 'La fecha y hora deben estar en formato correcto (YYYY-MM-DD HH:MM).',
            'scheduled_at.after' => 'La cita no puede ser en el pasado.',
            'scheduled_at.unique' => 'El médico ya tiene una cita a esta hora.',
            'status.required' => 'El estado es obligatorio.',
        ];
    }
}
