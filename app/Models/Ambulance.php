<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ambulance extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'internal_code',
    'plate_number',
    'brand',
    'model',
    'year',
    'current_mileage',
    'base_location',
    'status',
    'last_service_at',
    'next_service_at',
    'notes',
    'created_by',
  ];

  protected $casts = [
    'last_service_at' => 'date',
    'next_service_at' => 'date',
  ];

  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function transfers(): HasMany
  {
    return $this->hasMany(EmergencyTransfer::class);
  }

  public function activeTransfers(): HasMany
  {
    return $this->hasMany(EmergencyTransfer::class)
      ->whereIn('status', ['assigned', 'in_progress']);
  }

  public function getStatusLabel(): string
  {
    return match ($this->status) {
      'available' => 'Disponible',
      'in_transfer' => 'En traslado',
      'maintenance' => 'En mantenimiento',
      'out_of_service' => 'Fuera de servicio',
      default => 'Desconocido',
    };
  }
}
