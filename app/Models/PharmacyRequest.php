<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PharmacyRequest extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'requested_by',
    'patient_id',
    'appointment_id',
    'processed_by',
    'priority',
    'status',
    'notes',
    'pharmacy_notes',
    'requested_at',
    'processed_at',
    'completed_at',
  ];

  protected $casts = [
    'requested_at' => 'datetime',
    'processed_at' => 'datetime',
    'completed_at' => 'datetime',
  ];

  /**
   * Get the user who requested
   */
  public function requestedBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'requested_by');
  }

  /**
   * Get the user who processed
   */
  public function processedBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'processed_by');
  }

  /**
   * Get the related patient
   */
  public function patient(): BelongsTo
  {
    return $this->belongsTo(Patient::class);
  }

  /**
   * Get the related appointment
   */
  public function appointment(): BelongsTo
  {
    return $this->belongsTo(Appointment::class);
  }

  /**
   * Get the request items
   */
  public function items(): HasMany
  {
    return $this->hasMany(PharmacyRequestItem::class);
  }

  /**
   * Get the stock movements related to this request
   */
  public function stockMovements(): HasMany
  {
    return $this->hasMany(PharmacyStockMovement::class);
  }

  /**
   * Get priority label in Spanish
   */
  public function getPriorityLabel(): string
  {
    return match ($this->priority) {
      'low' => 'Baja',
      'normal' => 'Normal',
      'high' => 'Alta',
      'urgent' => 'Urgente',
      default => $this->priority,
    };
  }

  /**
   * Get status label in Spanish
   */
  public function getStatusLabel(): string
  {
    return match ($this->status) {
      'pending' => 'Pendiente',
      'processing' => 'En Proceso',
      'completed' => 'Completada',
      'cancelled' => 'Cancelada',
      default => $this->status,
    };
  }

  /**
   * Scope to get pending requests
   */
  public function scopePending($query)
  {
    return $query->where('status', 'pending');
  }

  /**
   * Scope to get processing requests
   */
  public function scopeProcessing($query)
  {
    return $query->where('status', 'processing');
  }

  /**
   * Scope to get urgent requests
   */
  public function scopeUrgent($query)
  {
    return $query->where('priority', 'urgent');
  }
}
