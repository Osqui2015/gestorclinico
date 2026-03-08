<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
  use HasFactory;

  // Tipos de habitación
  const TYPE_STANDARD = 'standard';
  const TYPE_INTENSIVE_CARE = 'intensive_care';
  const TYPE_ISOLATION = 'isolation';
  const TYPE_PEDIATRIC = 'pediatric';
  const TYPE_PSYCHIATRIC = 'psychiatric';
  const TYPE_RECOVERY = 'recovery';

  protected $fillable = [
    'name',
    'code',
    'room_type',
    'floor',
    'wing',
    'max_beds',
    'description',
    'is_active',
  ];

  protected $casts = [
    'is_active' => 'boolean',
    'max_beds' => 'integer',
    'floor' => 'integer',
  ];

  /**
   * Relación con camas
   */
  public function beds(): HasMany
  {
    return $this->hasMany(Bed::class);
  }

  /**
   * Camas activas
   */
  public function activeBeds(): HasMany
  {
    return $this->hasMany(Bed::class)->where('is_active', true);
  }

  /**
   * Camas disponibles
   */
  public function availableBeds(): HasMany
  {
    return $this->hasMany(Bed::class)
      ->where('status', Bed::STATUS_AVAILABLE)
      ->where('is_active', true);
  }

  /**
   * Camas ocupadas
   */
  public function occupiedBeds(): HasMany
  {
    return $this->hasMany(Bed::class)->where('status', Bed::STATUS_OCCUPIED);
  }

  /**
   * Verifica si hay camas disponibles
   */
  public function hasAvailableBeds(): bool
  {
    return $this->availableBeds()->exists();
  }

  /**
   * Cuenta de camas disponibles
   */
  public function getAvailableBedsCountAttribute(): int
  {
    return $this->availableBeds()->count();
  }

  /**
   * Cuenta de camas ocupadas
   */
  public function getOccupiedBedsCountAttribute(): int
  {
    return $this->occupiedBeds()->count();
  }

  /**
   * Porcentaje de ocupación
   */
  public function getOccupancyRateAttribute(): float
  {
    $totalBeds = $this->activeBeds()->count();
    if ($totalBeds === 0) {
      return 0;
    }
    return ($this->occupied_beds_count / $totalBeds) * 100;
  }

  /**
   * Scope: Habitaciones activas
   */
  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  /**
   * Scope: Por tipo de habitación
   */
  public function scopeOfType($query, string $type)
  {
    return $query->where('room_type', $type);
  }

  /**
   * Scope: Por piso
   */
  public function scopeOnFloor($query, int $floor)
  {
    return $query->where('floor', $floor);
  }

  /**
   * Scope: Por ala/sector
   */
  public function scopeInWing($query, string $wing)
  {
    return $query->where('wing', $wing);
  }
}
