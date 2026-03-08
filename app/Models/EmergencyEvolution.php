<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyEvolution extends Model
{
  protected $fillable = [
    'emergency_admission_id',
    'recorded_by',
    'recorded_at',
    'systolic_pressure',
    'diastolic_pressure',
    'heart_rate',
    'respiratory_rate',
    'temperature',
    'oxygen_saturation',
    'glucose',
    'clinical_notes',
    'treatment_notes',
    'medications_given',
    'tests_performed',
  ];

  protected $casts = [
    'recorded_at' => 'datetime',
  ];

  // Relaciones
  public function emergencyAdmission(): BelongsTo
  {
    return $this->belongsTo(EmergencyAdmission::class);
  }

  public function recordedBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'recorded_by');
  }
}
