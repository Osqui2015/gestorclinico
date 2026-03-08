<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Hospitalization extends Model
{
  use HasFactory, SoftDeletes;

  // Estados de internación
  const STATUS_ACTIVE = 'active';
  const STATUS_DISCHARGED = 'discharged';
  const STATUS_TRANSFERRED = 'transferred';
  const STATUS_DECEASED = 'deceased';

  // Tipos de admisión
  const TYPE_EMERGENCY = 'emergency';
  const TYPE_SCHEDULED = 'scheduled';
  const TYPE_POST_SURGICAL = 'post_surgical';
  const TYPE_TRANSFER = 'transfer';

  protected $fillable = [
    'patient_id',
    'bed_id',
    'operation_id',
    'doctor_id',
    'admission_date',
    'expected_discharge_date',
    'actual_discharge_date',
    'admission_reason',
    'admission_type',
    'status',
    'discharge_notes',
    'discharge_authorized_by',
    'discharged_by',
    'diagnosis',
    'treatment',
    'daily_observations',
  ];

  protected $casts = [
    'admission_date' => 'datetime',
    'expected_discharge_date' => 'date',
    'actual_discharge_date' => 'datetime',
  ];

  /**
   * Relación con paciente
   */
  public function patient(): BelongsTo
  {
    return $this->belongsTo(Patient::class);
  }

  /**
   * Relación con cama
   */
  public function bed(): BelongsTo
  {
    return $this->belongsTo(Bed::class);
  }

  /**
   * Relación con operación (si viene de post-quirúrgico)
   */
  public function operation(): BelongsTo
  {
    return $this->belongsTo(Operation::class);
  }

  /**
   * Médico responsable
   */
  public function doctor(): BelongsTo
  {
    return $this->belongsTo(User::class, 'doctor_id');
  }

  /**
   * Médico que autorizó el alta
   */
  public function dischargeAuthorizer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'discharge_authorized_by');
  }

  /**
   * Usuario que ejecutó el alta
   */
  public function discharger(): BelongsTo
  {
    return $this->belongsTo(User::class, 'discharged_by');
  }

  /**
   * Verifica si la internación está activa
   */
  public function isActive(): bool
  {
    return $this->status === self::STATUS_ACTIVE;
  }

  /**
   * Verifica si ya fue dado de alta
   */
  public function isDischarged(): bool
  {
    return $this->status === self::STATUS_DISCHARGED;
  }

  /**
   * Verifica si puede dar el alta (fecha alcanzada o pasada)
   */
  public function canDischarge(): bool
  {
    if (!$this->isActive()) {
      return false;
    }

    if (!$this->expected_discharge_date) {
      return false;
    }

    return Carbon::parse($this->expected_discharge_date)->lte(now());
  }

  /**
   * Verifica si tiene autorización del médico para el alta
   */
  public function hasDischargeAuthorization(): bool
  {
    return !is_null($this->discharge_authorized_by);
  }

  /**
   * Dar alta al paciente
   */
  public function discharge(int $userId, ?string $notes = null): void
  {
    if (!$this->isActive()) {
      throw new \Exception('La internación no está activa.');
    }

    $this->update([
      'status' => self::STATUS_DISCHARGED,
      'actual_discharge_date' => now(),
      'discharged_by' => $userId,
      'discharge_notes' => $notes ?? $this->discharge_notes,
    ]);

    // Marcar cama como pendiente de limpieza
    $this->bed->markAsPendingCleaning();
  }

  /**
   * Transferir a otra cama
   */
  public function transferToBed(int $newBedId, ?string $reason = null): Hospitalization
  {
    if (!$this->isActive()) {
      throw new \Exception('La internación no está activa.');
    }

    // Marcar internación actual como transferida
    $this->update([
      'status' => self::STATUS_TRANSFERRED,
      'discharge_notes' => $reason ?? 'Transferencia a otra cama',
    ]);

    // Liberar cama actual
    $this->bed->markAsPendingCleaning();

    // Crear nueva internación en la nueva cama
    $newBed = Bed::findOrFail($newBedId);

    if (!$newBed->isAvailable()) {
      throw new \Exception('La cama de destino no está disponible.');
    }

    $newHospitalization = self::create([
      'patient_id' => $this->patient_id,
      'bed_id' => $newBedId,
      'operation_id' => $this->operation_id,
      'doctor_id' => $this->doctor_id,
      'admission_date' => now(),
      'expected_discharge_date' => $this->expected_discharge_date,
      'admission_reason' => $this->admission_reason . ' (Transferido)',
      'admission_type' => self::TYPE_TRANSFER,
      'diagnosis' => $this->diagnosis,
      'treatment' => $this->treatment,
      'daily_observations' => $this->daily_observations,
    ]);

    // Marcar nueva cama como ocupada
    $newBed->markAsOccupied();

    return $newHospitalization;
  }

  /**
   * Actualizar fecha estimada de alta (solo médico)
   */
  public function updateExpectedDischargeDate(string $date, int $authorizedBy): void
  {
    $this->update([
      'expected_discharge_date' => $date,
      'discharge_authorized_by' => $authorizedBy,
    ]);
  }

  /**
   * Calcular días de internación
   */
  public function getDaysHospitalizedAttribute(): int
  {
    $endDate = $this->actual_discharge_date ?? now();
    return $this->admission_date->diffInDays($endDate);
  }

  /**
   * Scope: Internaciones activas
   */
  public function scopeActive($query)
  {
    return $query->where('status', self::STATUS_ACTIVE);
  }

  /**
   * Scope: Por médico
   */
  public function scopeByDoctor($query, int $doctorId)
  {
    return $query->where('doctor_id', $doctorId);
  }

  /**
   * Scope: Por paciente
   */
  public function scopeByPatient($query, int $patientId)
  {
    return $query->where('patient_id', $patientId);
  }

  /**
   * Scope: Listas para alta
   */
  public function scopeReadyForDischarge($query)
  {
    return $query->where('status', self::STATUS_ACTIVE)
      ->whereNotNull('expected_discharge_date')
      ->whereDate('expected_discharge_date', '<=', now());
  }

  /**
   * Scope: Por tipo de admisión
   */
  public function scopeOfType($query, string $type)
  {
    return $query->where('admission_type', $type);
  }
}
