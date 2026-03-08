<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreAdmission extends Model
{
  use SoftDeletes;

  protected $table = 'pre_admissions';

  protected $fillable = [
    'operation_id',
    'patient_id',
    'secretary_id',
    'status',
    'urgent_number',
    'contact_phone',
    'emergency_contact_name',
    'emergency_contact_phone',
    'medical_history_verified',
    'patient_observations',
    'data_verified_at',
    'documentation_verified_at',
    'ready_for_surgery_at',
    'cancelled_at',
    'cancellation_reason',
  ];

  protected $casts = [
    'data_verified_at' => 'datetime',
    'documentation_verified_at' => 'datetime',
    'ready_for_surgery_at' => 'datetime',
    'cancelled_at' => 'datetime',
  ];

  const STATUS_PENDING_ASSIGNMENT = 'pending_assignment';
  const STATUS_DATA_PENDING = 'data_pending';
  const STATUS_DOCUMENTS_PENDING = 'documents_pending';
  const STATUS_READY = 'ready_for_surgery';
  const STATUS_CANCELLED = 'cancelled';

  /**
   * Relationship to Operation
   */
  public function operation(): BelongsTo
  {
    return $this->belongsTo(Operation::class);
  }

  /**
   * Relationship to Patient
   */
  public function patient(): BelongsTo
  {
    return $this->belongsTo(Patient::class);
  }

  /**
   * Relationship to Secretary (User)
   */
  public function secretary(): BelongsTo
  {
    return $this->belongsTo(User::class, 'secretary_id');
  }

  /**
   * Relationship to PreAdmissionDocuments
   */
  public function documents(): HasMany
  {
    return $this->hasMany(PreAdmissionDocument::class);
  }

  /**
   * Check if all required data is verified
   */
  public function isDataVerified(): bool
  {
    return !is_null($this->data_verified_at);
  }

  /**
   * Check if all documents are verified
   */
  public function areDocumentsVerified(): bool
  {
    $mandatoryDocumentIds = RequiredDocument::query()
      ->where('status', 'active')
      ->where('is_mandatory', true)
      ->pluck('id');

    if ($mandatoryDocumentIds->isEmpty()) {
      return true;
    }

    $verifiedMandatoryCount = $this->documents()
      ->whereIn('required_document_id', $mandatoryDocumentIds)
      ->whereIn('status', ['verified', 'not_applicable'])
      ->distinct('required_document_id')
      ->count('required_document_id');

    return $verifiedMandatoryCount === $mandatoryDocumentIds->count();
  }

  /**
   * Check if can transition to ready_for_surgery
   */
  public function canConfirmForSurgery(): bool
  {
    return $this->isDataVerified() && $this->areDocumentsVerified();
  }

  /**
   * Mark as ready for surgery (turns green)
   */
  public function confirmForSurgery(): bool
  {
    if (!$this->canConfirmForSurgery()) {
      return false;
    }

    $this->update([
      'status' => self::STATUS_READY,
      'documentation_verified_at' => now(),
      'ready_for_surgery_at' => now(),
    ]);

    // Update operation status to ready
    $this->operation->update(['status' => 'ready_for_surgery']);

    return true;
  }

  /**
   * Cancel pre-admission
   */
  public function cancel(string $reason): void
  {
    $this->update([
      'status' => self::STATUS_CANCELLED,
      'cancelled_at' => now(),
      'cancellation_reason' => $reason,
    ]);

    // Reset operation back to scheduled
    $this->operation->update(['status' => 'scheduled']);
  }

  /**
   * Scopes
   */
  public function scopePending($query)
  {
    return $query->whereIn('status', [
      self::STATUS_PENDING_ASSIGNMENT,
      self::STATUS_DATA_PENDING,
      self::STATUS_DOCUMENTS_PENDING,
    ]);
  }

  public function scopeReady($query)
  {
    return $query->where('status', self::STATUS_READY);
  }

  public function scopeAssignedTo($query, $secretaryId)
  {
    return $query->where('secretary_id', $secretaryId);
  }
}
