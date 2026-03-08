<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreAdmissionDocument extends Model
{
  use SoftDeletes;

  protected $table = 'pre_admission_documents';

  protected $fillable = [
    'pre_admission_id',
    'required_document_id',
    'status',
    'file_path',
    'original_filename',
    'file_size',
    'uploaded_at',
    'verified_at',
    'verification_notes',
    'rejection_reason',
  ];

  protected $casts = [
    'uploaded_at' => 'datetime',
    'verified_at' => 'datetime',
  ];

  const STATUS_PENDING = 'pending';
  const STATUS_UPLOADED = 'uploaded';
  const STATUS_VERIFIED = 'verified';
  const STATUS_REJECTED = 'rejected';
  const STATUS_NOT_APPLICABLE = 'not_applicable';

  /**
   * Relationship to PreAdmission
   */
  public function preAdmission(): BelongsTo
  {
    return $this->belongsTo(PreAdmission::class);
  }

  /**
   * Relationship to RequiredDocument
   */
  public function requiredDocument(): BelongsTo
  {
    return $this->belongsTo(RequiredDocument::class);
  }

  /**
   * Check if document is verified
   */
  public function isVerified(): bool
  {
    return $this->status === self::STATUS_VERIFIED;
  }

  /**
   * Mark as rejected
   */
  public function reject(string $reason): void
  {
    $this->update([
      'status' => self::STATUS_REJECTED,
      'rejection_reason' => $reason,
    ]);
  }

  /**
   * Mark as verified
   */
  public function verify(string $notes = null): void
  {
    $this->update([
      'status' => self::STATUS_VERIFIED,
      'verified_at' => now(),
      'verification_notes' => $notes,
    ]);
  }

  /**
   * Scopes
   */
  public function scopePending($query)
  {
    return $query->where('status', self::STATUS_PENDING);
  }

  public function scopeVerified($query)
  {
    return $query->where('status', self::STATUS_VERIFIED);
  }
}
