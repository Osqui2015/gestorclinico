<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bed extends Model
{
  use HasFactory;

  // Estados de cama
  const STATUS_AVAILABLE = 'available';
  const STATUS_OCCUPIED = 'occupied';
  const STATUS_PENDING_CLEANING = 'pending_cleaning';
  const STATUS_CLEANING = 'cleaning';
  const STATUS_MAINTENANCE = 'maintenance';

  // Tipos de cama
  const TYPE_STANDARD = 'standard';
  const TYPE_INTENSIVE_CARE = 'intensive_care';
  const TYPE_ISOLATION = 'isolation';
  const TYPE_PEDIATRIC = 'pediatric';
  const TYPE_PSYCHIATRIC = 'psychiatric';

  protected $fillable = [
    'room_id',
    'bed_number',
    'status',
    'bed_type',
    'observations',
    'is_active',
  ];

  protected $casts = [
    'is_active' => 'boolean',
  ];

  /**
   * Relación con Room (sala/habitación)
   */
  public function room(): BelongsTo
  {
    return $this->belongsTo(Room::class);
  }

  /**
   * Relación con internaciones
   */
  public function hospitalizations(): HasMany
  {
    return $this->hasMany(Hospitalization::class);
  }

  /**
   * Internación activa actual
   */
  public function currentHospitalization(): HasOne
  {
    return $this->hasOne(Hospitalization::class)
      ->where('status', Hospitalization::STATUS_ACTIVE)
      ->latestOfMany();
  }

  /**
   * Registros de limpieza
   */
  public function cleaningLogs(): HasMany
  {
    return $this->hasMany(BedCleaningLog::class);
  }

  /**
   * Último registro de limpieza
   */
  public function lastCleaning(): HasOne
  {
    return $this->hasOne(BedCleaningLog::class)->latestOfMany('completed_at');
  }

  /**
   * Verifica si la cama está disponible
   */
  public function isAvailable(): bool
  {
    return $this->status === self::STATUS_AVAILABLE && $this->is_active;
  }

  /**
   * Verifica si la cama está ocupada
   */
  public function isOccupied(): bool
  {
    return $this->status === self::STATUS_OCCUPIED;
  }

  /**
   * Verifica si necesita limpieza
   */
  public function needsCleaning(): bool
  {
    return $this->status === self::STATUS_PENDING_CLEANING;
  }

  /**
   * Marcar cama como ocupada
   */
  public function markAsOccupied(): void
  {
    $this->update(['status' => self::STATUS_OCCUPIED]);
  }

  /**
   * Marcar cama como disponible
   */
  public function markAsAvailable(): void
  {
    $this->update(['status' => self::STATUS_AVAILABLE]);
  }

  /**
   * Marcar cama como pendiente de limpieza
   */
  public function markAsPendingCleaning(): void
  {
    $this->update(['status' => self::STATUS_PENDING_CLEANING]);
  }

  /**
   * Iniciar limpieza
   */
  public function startCleaning(): void
  {
    $this->update(['status' => self::STATUS_CLEANING]);
  }

  /**
   * Completar limpieza y marcar como disponible
   */
  public function completeCleaning(int $userId, string $cleaningType = 'routine', ?string $notes = null): void
  {
    BedCleaningLog::create([
      'bed_id' => $this->id,
      'cleaned_by' => $userId,
      'started_at' => $this->status === self::STATUS_CLEANING ? $this->updated_at : now(),
      'completed_at' => now(),
      'cleaning_type' => $cleaningType,
      'notes' => $notes,
    ]);

    $this->markAsAvailable();
  }

  /**
   * Scope: Camas disponibles
   */
  public function scopeAvailable($query)
  {
    return $query->where('status', self::STATUS_AVAILABLE)
      ->where('is_active', true);
  }

  /**
   * Scope: Camas ocupadas
   */
  public function scopeOccupied($query)
  {
    return $query->where('status', self::STATUS_OCCUPIED);
  }

  /**
   * Scope: Camas pendientes de limpieza
   */
  public function scopePendingCleaning($query)
  {
    return $query->where('status', self::STATUS_PENDING_CLEANING);
  }

  /**
   * Scope: Camas por tipo
   */
  public function scopeOfType($query, string $type)
  {
    return $query->where('bed_type', $type);
  }

  /**
   * Scope: Camas activas
   */
  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  /**
   * Obtener nombre completo de la cama (Sala + Número)
   */
  public function getFullNameAttribute(): string
  {
    return "{$this->room->name} - Cama {$this->bed_number}";
  }
}
