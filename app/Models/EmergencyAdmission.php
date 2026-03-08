<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyAdmission extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'patient_id',
    'attending_doctor_id',
    'nurse_id',
    'admission_time',
    'triage_time',
    'discharged_at',
    'triage_level',
    'chief_complaint',
    'triage_notes',
    'systolic_pressure',
    'diastolic_pressure',
    'heart_rate',
    'respiratory_rate',
    'temperature',
    'oxygen_saturation',
    'glucose',
    'consciousness_level',
    'status',
    'preliminary_diagnosis',
    'treatment_given',
    'discharge_diagnosis',
    'discharge_instructions',
    'observations',
    'clinical_evolution',
  ];

  protected $casts = [
    'admission_time' => 'datetime',
    'triage_time' => 'datetime',
    'discharged_at' => 'datetime',
  ];

  // Relaciones
  public function patient(): BelongsTo
  {
    return $this->belongsTo(Patient::class);
  }

  public function attendingDoctor(): BelongsTo
  {
    return $this->belongsTo(User::class, 'attending_doctor_id');
  }

  public function nurse(): BelongsTo
  {
    return $this->belongsTo(User::class, 'nurse_id');
  }

  public function evolutions(): HasMany
  {
    return $this->hasMany(EmergencyEvolution::class);
  }

  // Helpers
  public function getTriageLevelName(): string
  {
    return match ($this->triage_level) {
      '1' => '🔴 Resucitación (Inmediato)',
      '2' => '🟠 Emergencia (10 min)',
      '3' => '🟡 Urgencia (30 min)',
      '4' => '🟢 Menos urgente (60 min)',
      '5' => '🔵 No urgente (120 min)',
      default => 'Desconocido'
    };
  }

  public function getStatusName(): string
  {
    return match ($this->status) {
      'waiting' => 'Esperando',
      'in_care' => 'En atención',
      'observation' => 'En observación',
      'discharged' => 'Dado de alta',
      'admitted' => 'Internado',
      'transferred' => 'Trasladado',
      default => 'Desconocido'
    };
  }

  public function timeInEmergency(): string
  {
    $end = $this->discharged_at ?? now();
    $diff = $end->diffInMinutes($this->admission_time);

    if ($diff < 60) {
      return "{$diff}m";
    }

    $hours = intdiv($diff, 60);
    $minutes = $diff % 60;
    return "{$hours}h {$minutes}m";
  }
}
