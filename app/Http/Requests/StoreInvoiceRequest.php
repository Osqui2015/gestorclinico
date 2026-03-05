<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
      'patient_id' => 'required|exists:patients,id',
      'appointment_id' => 'nullable|exists:appointments,id',
      'health_insurance_id' => 'nullable|exists:health_insurances,id',
      'invoice_date' => 'required|date',
      'items' => 'required|array|min:1',
      'items.*.description' => 'required|string|max:255',
      'items.*.quantity' => 'required|integer|min:1',
      'items.*.unit_price' => 'required|numeric|min:0',
      'discount' => 'nullable|numeric|min:0',
      'insurance_coverage' => 'nullable|numeric|min:0',
      'notes' => 'nullable|string',
      'payment_method' => 'nullable|in:cash,card,transfer,insurance,other',
    ];
  }

  /**
   * Get custom attributes for validator errors.
   */
  public function attributes(): array
  {
    return [
      'patient_id' => 'paciente',
      'appointment_id' => 'turno',
      'health_insurance_id' => 'obra social',
      'invoice_date' => 'fecha de factura',
      'items' => 'conceptos',
      'items.*.description' => 'descripción',
      'items.*.quantity' => 'cantidad',
      'items.*.unit_price' => 'precio unitario',
      'discount' => 'descuento',
      'insurance_coverage' => 'cobertura',
      'notes' => 'notas',
      'payment_method' => 'método de pago',
    ];
  }
}
