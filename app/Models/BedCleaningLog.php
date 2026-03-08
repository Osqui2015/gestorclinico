<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BedCleaningLog extends Model
{
  use HasFactory;

  // Tipos de limpieza
  const TYPE_ROUTINE = 'routine';
  const TYPE_DEEP = 'deep';
  const TYPE_DISCHARGE = 'discharge';
  const TYPE_DISINFECTION = 'disinfection';

  protected $fillable = [
    'bed_id',
    'cleaned_by',
    'started_at',
    'completed_at',
    'cleaning_type',
    'notes',
  ];

  protected $casts = [
    'started_at' => 'datetime',
    'completed_at' => 'datetime',
  ];

  /**
   * Relación con cama
   */
  public function bed(): BelongsTo
  {
    return $this->belongsTo(Bed::class);
  }

  /**
   * Usuario que realizó la limpieza
   */
  public function cleaner(): BelongsTo
  {
    return $this->belongsTo(User::class, 'cleaned_by');
  }

  /**
   * Calcular duración de limpieza en minutos
   */
  public function getDurationInMinutesAttribute(): ?int
  {
    if (!$this->started_at) {
      return null;
    }

    return $this->started_at->diffInMinutes($this->completed_at);
  }

  /**
   * Scope: Por tipo de limpieza
   */
  public function scopeOfType($query, string $type)
  {
    return $query->where('cleaning_type', $type);
  }

  /**
   * Scope: Por cama
   */
  public function scopeForBed($query, int $bedId)
  {
    return $query->where('bed_id', $bedId);
  }

  /**
   * Scope: Limpiezas recientes
   */
  public function scopeRecent($query, int $days = 7)
  {
    return $query->where('completed_at', '>=', now()->subDays($days));
  }
}
