<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class MedicalRecord extends Model
{
    /** @use HasFactory<\Database\Factories\MedicalRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'reason',
        'diagnosis',
        'treatment',
        'private_notes',
        'is_first_consultation',
        'health_background',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'json',
        'is_first_consultation' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the patient for this medical record
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor (user) who created this record
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the prescriptions for this medical record
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get full doctor name
     */
    public function getDoctorNameAttribute(): string
    {
        return $this->doctor?->name ?? 'N/A';
    }

    /**
     * Encrypt private notes on set and decrypt on get to keep them at rest encrypted.
     */
    public function getPrivateNotesAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            // If decryption fails, return raw value to avoid breaking reads
            return $value;
        }
    }

    public function setPrivateNotesAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['private_notes'] = null;
            return;
        }

        try {
            $this->attributes['private_notes'] = Crypt::encryptString((string) $value);
        } catch (\Throwable $e) {
            // Fallback to storing raw (should not happen)
            $this->attributes['private_notes'] = $value;
        }
    }
}
