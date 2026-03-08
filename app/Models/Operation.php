<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operation extends Model
{
  use HasFactory, SoftDeletes;

  public const DEFAULT_CLEANING_MARGIN = 15;

  protected $fillable = [
    'operation_room_id',
    'doctor_id',
    'patient_id',
    'operation_type',
    'scheduled_start',
    'scheduled_end',
    'estimated_duration_minutes',
    'cleaning_margin_minutes',
    'urgency',
    'status',
    'clinical_notes',
    'pharmacy_notes',
    'created_by',
    'updated_by',
    'cancelled_by',
    'cancelled_at',
    'cancellation_reason',
  ];

  protected $casts = [
    'scheduled_start' => 'datetime',
    'scheduled_end' => 'datetime',
    'cancelled_at' => 'datetime',
    'estimated_duration_minutes' => 'integer',
    'cleaning_margin_minutes' => 'integer',
  ];

  /**
   * Linked operation room.
   */
  public function room(): BelongsTo
  {
    return $this->belongsTo(OperationRoom::class, 'operation_room_id');
  }

  /**
   * Assigned doctor.
   */
  public function doctor(): BelongsTo
  {
    return $this->belongsTo(User::class, 'doctor_id');
  }

  /**
   * Related patient.
   */
  public function patient(): BelongsTo
  {
    return $this->belongsTo(Patient::class);
  }

  /**
   * User who created this operation.
   */
  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  /**
   * User who last updated this operation.
   */
  public function updater(): BelongsTo
  {
    return $this->belongsTo(User::class, 'updated_by');
  }

  /**
   * User who cancelled this operation.
   */
  public function canceller(): BelongsTo
  {
    return $this->belongsTo(User::class, 'cancelled_by');
  }

  /**
   * Requested pharmacy items for this operation.
   */
  public function pharmacyItems(): HasMany
  {
    return $this->hasMany(OperationPharmacyItem::class);
  }

  /**
   * Pre-admission for this operation.
   */
  public function preAdmission()
  {
    return $this->hasOne(PreAdmission::class);
  }

  /**
   * Hospitalization for this operation (post-surgical).
   */
  public function hospitalization()
  {
    return $this->hasOne(Hospitalization::class);
  }

  /**
   * Scope scheduled/in progress operations only.
   */
  public function scopeAgendaActive($query)
  {
    return $query->whereIn('status', ['scheduled', 'in_progress']);
  }

  /**
   * Scope for range-based agenda filtering.
   */
  public function scopeBetweenDates($query, Carbon $from, Carbon $to)
  {
    return $query->where('scheduled_start', '<', $to)
      ->where('scheduled_end', '>', $from);
  }

  /**
   * Returns a Spanish label for status.
   */
  public function getStatusLabel(): string
  {
    return match ($this->status) {
      'scheduled' => 'Programada',
      'in_progress' => 'En curso',
      'completed' => 'Completada',
      'cancelled' => 'Cancelada',
      default => $this->status,
    };
  }

  /**
   * Returns a Spanish label for urgency.
   */
  public function getUrgencyLabel(): string
  {
    return match ($this->urgency) {
      'scheduled' => 'Programada',
      'urgent' => 'Urgente',
      'emergency' => 'Emergencia',
      default => $this->urgency,
    };
  }
}
