<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyTransfer extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'patient_id',
    'requested_by',
    'ambulance_id',
    'origin',
    'destination',
    'transfer_type',
    'priority',
    'status',
    'requested_at',
    'assigned_at',
    'departed_at',
    'arrived_at',
    'clinical_summary',
    'crew_notes',
  ];

  protected $casts = [
    'requested_at' => 'datetime',
    'assigned_at' => 'datetime',
    'departed_at' => 'datetime',
    'arrived_at' => 'datetime',
  ];

  public function patient(): BelongsTo
  {
    return $this->belongsTo(Patient::class);
  }

  public function requester(): BelongsTo
  {
    return $this->belongsTo(User::class, 'requested_by');
  }

  public function ambulance(): BelongsTo
  {
    return $this->belongsTo(Ambulance::class);
  }

  public function getPriorityLabel(): string
  {
    return match ($this->priority) {
      'low' => 'Baja',
      'medium' => 'Media',
      'high' => 'Alta',
      'critical' => 'Critica',
      default => 'Desconocida',
    };
  }

  public function getStatusLabel(): string
  {
    return match ($this->status) {
      'requested' => 'Solicitado',
      'assigned' => 'Asignado',
      'in_progress' => 'En traslado',
      'completed' => 'Completado',
      'cancelled' => 'Cancelado',
      default => 'Desconocido',
    };
  }
}
