<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    /** @use HasFactory<\Database\Factories\PrescriptionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'medical_record_id',
        'medications',
        'instructions',
        'diagnosis',
        'notes',
        'status',
        // Campos ReNaPDiS
        'cuir',
        'matricula_profesional',
        'matricula_tipo',
        'profesional_nombre_completo',
        'profesional_especialidad',
        'consultorio_direccion',
        'paciente_cuil',
        'paciente_nombre_completo',
        'paciente_fecha_nacimiento',
        'obra_social',
        'numero_afiliado',
        'medicamentos_genericos',
        'cie10_codigo',
        'cie10_descripcion',
        'fecha_emision',
        'fecha_vencimiento',
        'estado_dispensacion',
        'fecha_dispensacion',
        'farmacia_dispensadora',
        'firma_electronica_hash',
        'firma_metodo',
        'firma_timestamp',
        'firma_ip_address',
        'qr_code_path',
        'qr_code_data',
        'validado_refeps',
        'validado_renaper',
        'fecha_validacion_externa',
        'log_modificaciones',
    ];

    protected $casts = [
        'medications' => 'json',
        'instructions' => 'json',
        'medicamentos_genericos' => 'json',
        'log_modificaciones' => 'json',
        'paciente_fecha_nacimiento' => 'date',
        'fecha_emision' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'fecha_dispensacion' => 'datetime',
        'firma_timestamp' => 'datetime',
        'fecha_validacion_externa' => 'datetime',
        'validado_refeps' => 'boolean',
        'validado_renaper' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the patient for this prescription
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor who created this prescription
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the appointment associated with this prescription
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the medical record associated with this prescription
     */
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * Check if prescription is expired
     */
    public function isExpired(): bool
    {
        if (!$this->fecha_vencimiento) {
            return false;
        }
        return now()->isAfter($this->fecha_vencimiento);
    }

    /**
     * Check if prescription is valid (not expired and pending)
     */
    public function isValid(): bool
    {
        return !$this->isExpired() &&
            $this->estado_dispensacion === 'pendiente' &&
            $this->cuir !== null;
    }

    /**
     * Check if prescription is dispensed
     */
    public function isDispensed(): bool
    {
        return $this->estado_dispensacion === 'dispensada';
    }

    /**
     * Check if prescription complies with ReNaPDiS
     */
    public function isReNaPDiSCompliant(): bool
    {
        return $this->cuir !== null &&
            $this->validado_refeps &&
            $this->validado_renaper &&
            $this->firma_electronica_hash !== null &&
            $this->medicamentos_genericos !== null &&
            $this->cie10_codigo !== null;
    }

    /**
     * Get days until expiration
     */
    public function daysUntilExpiration(): ?int
    {
        if (!$this->fecha_vencimiento) {
            return null;
        }
        return now()->diffInDays($this->fecha_vencimiento, false);
    }
}
