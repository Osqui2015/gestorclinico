<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceOrder extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'medical_equipment_id',
    'reported_by',
    'assigned_to',
    'title',
    'description',
    'priority',
    'status',
    'reported_at',
    'started_at',
    'completed_at',
    'resolution_notes',
    'cost',
  ];

  protected $casts = [
    'reported_at' => 'datetime',
    'started_at' => 'datetime',
    'completed_at' => 'datetime',
    'cost' => 'decimal:2',
  ];

  public function equipment(): BelongsTo
  {
    return $this->belongsTo(MedicalEquipment::class, 'medical_equipment_id');
  }

  public function reporter(): BelongsTo
  {
    return $this->belongsTo(User::class, 'reported_by');
  }

  public function assignee(): BelongsTo
  {
    return $this->belongsTo(User::class, 'assigned_to');
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
      'open' => 'Abierta',
      'in_progress' => 'En progreso',
      'on_hold' => 'En espera',
      'completed' => 'Completada',
      'cancelled' => 'Cancelada',
      default => 'Desconocido',
    };
  }
}
