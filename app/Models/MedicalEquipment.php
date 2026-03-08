<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalEquipment extends Model
{
  use SoftDeletes;

  protected $table = 'medical_equipments';

  protected $fillable = [
    'name',
    'code',
    'category',
    'brand',
    'model',
    'serial_number',
    'location',
    'status',
    'last_maintenance_at',
    'next_maintenance_at',
    'notes',
    'created_by',
  ];

  protected $casts = [
    'last_maintenance_at' => 'datetime',
    'next_maintenance_at' => 'datetime',
  ];

  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function maintenanceOrders(): HasMany
  {
    return $this->hasMany(MaintenanceOrder::class);
  }

  public function openOrders(): HasMany
  {
    return $this->hasMany(MaintenanceOrder::class)
      ->whereIn('status', ['open', 'in_progress', 'on_hold']);
  }

  public function getStatusLabel(): string
  {
    return match ($this->status) {
      'operational' => 'Operativo',
      'maintenance_required' => 'Requiere mantenimiento',
      'in_maintenance' => 'En mantenimiento',
      'out_of_service' => 'Fuera de servicio',
      default => 'Desconocido',
    };
  }
}
