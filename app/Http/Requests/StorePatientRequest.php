<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dni' => 'required|string|unique:patients,dni|regex:/^\d{6,}$/',
            'cuil' => 'required|string|unique:patients,cuil|regex:/^\d{2}-?\d{8}-?\d$/',
            'birth_date' => 'required|date|before:today',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'allergies' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'health_insurance_id' => 'nullable|exists:health_insurances,id',
            'member_number' => 'nullable|string|max:50',
            'new_health_insurance_name' => 'nullable|string|max:255',
            'new_health_insurance_code' => 'nullable|string|max:50',
            'obra_social_data' => 'nullable|array',
            'obra_social_data.rnos' => 'nullable|string|max:50',
            'obra_social_data.nombre' => 'nullable|string|max:255',
            'obra_social_data.sigla' => 'nullable|string|max:50',
            'obra_social_data.provincia' => 'nullable|string|max:255',
            'obra_social_data.localidad' => 'nullable|string|max:255',
            'obra_social_data.telefonos' => 'nullable|array',
            'obra_social_data.telefonos.*' => 'nullable|string|max:50',
            'obra_social_data.emails' => 'nullable|array',
            'obra_social_data.emails.*' => 'nullable|email|max:255',
            'obra_social_data.domicilio' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'dni.regex' => 'El DNI debe contener al menos 6 dígitos.',
            'dni.unique' => 'El DNI ya está registrado en el sistema.',
            'cuil.regex' => 'El CUIL debe tener 11 dígitos.',
            'cuil.unique' => 'El CUIL ya está registrado en el sistema.',
            'birth_date.before' => 'La fecha de nacimiento no puede ser igual o posterior a hoy.',
        ];
    }
}
