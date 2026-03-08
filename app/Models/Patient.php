<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'dni',
        'cuil',
        'birth_date',
        'phone',
        'email',
        'address',
        'city',
        'zip_code',
        'gender',
        'emergency_contact_name',
        'emergency_contact_phone',
        'allergies',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    protected $appends = [
        'full_name',
        'age',
    ];

    /**
     * Get all medical records for this patient
     */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class)->orderByDesc('created_at');
    }

    /**
     * Get appointments for this patient
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get prescriptions for this patient
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class)->orderByDesc('created_at');
    }

    /**
     * Get health insurances for this patient
     */
    public function healthInsurances(): BelongsToMany
    {
        return $this->belongsToMany(HealthInsurance::class, 'patient_insurance')
            ->withPivot('member_number', 'valid_from', 'valid_until', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Get primary insurance
     */
    public function primaryInsurance()
    {
        return $this->healthInsurances()
            ->wherePivot('is_primary', true)
            ->first();
    }

    /**
     * Get invoices for this patient
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get operations for this patient.
     */
    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    /**
     * Get pre-admissions for this patient.
     */
    public function preAdmissions(): HasMany
    {
        return $this->hasMany(PreAdmission::class);
    }

    /**
     * Get hospitalizations for this patient.
     */
    public function hospitalizations(): HasMany
    {
        return $this->hasMany(Hospitalization::class);
    }

    /**
     * Get current active hospitalization.
     */
    public function currentHospitalization()
    {
        return $this->hasOne(Hospitalization::class)
            ->where('status', Hospitalization::STATUS_ACTIVE)
            ->latestOfMany();
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get age
     */
    public function getAgeAttribute(): int
    {
        return $this->birth_date->age;
    }

    /**
     * Check if patient has allergies
     */
    public function hasAllergies(): bool
    {
        return !empty($this->allergies);
    }

    /**
     * Get allergies as array
     */
    public function getAllergiesListAttribute(): array
    {
        if (empty($this->allergies)) {
            return [];
        }
        return array_map('trim', explode(',', $this->allergies));
    }

    /**
     * Get patient account (cuentas corrientes)
     */
    public function account()
    {
        return $this->hasOne(PatientAccount::class);
    }

    /**
     * Get emergency admissions for this patient.
     */
    public function emergencyAdmissions(): HasMany
    {
        return $this->hasMany(EmergencyAdmission::class);
    }
}
