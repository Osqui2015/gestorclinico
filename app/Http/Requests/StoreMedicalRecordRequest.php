<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && ($user->isDoctor() || $user->isAdmin());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => 'nullable|string|max:1000',
            'diagnosis' => 'nullable|string|max:2000',
            'treatment' => 'nullable|string|max:2000',
            'private_notes' => 'nullable|string|max:2000',
            'is_first_consultation' => 'nullable|boolean',
            'health_background' => 'nullable|string|max:4000|required_if:is_first_consultation,true',
            'attachments' => 'nullable|json',
            'prescriptions' => 'nullable|array',
            'prescriptions.*.diagnosis' => 'required|string',
            'prescriptions.*.medications' => 'required|array|min:1',
            'prescriptions.*.medications.*.name' => 'required|string',
            'prescriptions.*.medications.*.dosage' => 'required|string',
            'prescriptions.*.medications.*.frequency' => 'required|string',
            'prescriptions.*.medications.*.duration' => 'required|string',
            'prescriptions.*.instructions' => 'required|array|min:1',
            'prescriptions.*.instructions.*.description' => 'required|string',
            'prescriptions.*.notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.max' => 'El motivo de consulta no puede exceder 1000 caracteres.',
            'diagnosis.max' => 'El diagnóstico no puede exceder 2000 caracteres.',
            'treatment.max' => 'El tratamiento no puede exceder 2000 caracteres.',
            'private_notes.max' => 'Las notas privadas no pueden exceder 2000 caracteres.',
            'health_background.required_if' => 'Si es primera consulta, debes completar los antecedentes de salud.',
            'health_background.max' => 'Los antecedentes de salud no pueden exceder 4000 caracteres.',
        ];
    }
}
