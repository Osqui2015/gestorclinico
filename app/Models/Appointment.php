<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'health_insurance_id',
        'coseguro',
        'scheduled_at',
        'duration',
        'status',
        'reason',
        'notes',
        'is_walk_in',
        'confirmed',
        'confirmed_at',
        'checked_in_at',
        'no_show_count',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'status' => 'string',
        'is_walk_in' => 'boolean',
        'confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the doctor (user) who has this appointment
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the patient for this appointment
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the health insurance for this appointment
     */
    public function healthInsurance(): BelongsTo
    {
        return $this->belongsTo(HealthInsurance::class);
    }

    /**
     * Get invoice for this appointment
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Get prescriptions for this appointment
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Check if appointment is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    /**
     * Check if patient has checked in
     */
    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    /**
     * Mark appointment as confirmed
     */
    public function markAsConfirmed(): void
    {
        $this->update([
            'confirmed' => true,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Check in patient
     */
    public function checkIn(): void
    {
        $this->update([
            'checked_in_at' => now(),
            'status' => 'pending',
        ]);
    }

    /**
     * Cancel appointment
     */
    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Get
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'gray',
            'called' => 'yellow',
            'attending' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get status label in Spanish
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'called' => 'Llamado',
            'attending' => 'Atendiendo',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
            default => 'Desconocido'
        };
    }
}
