<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequiredDocument extends Model
{
  protected $table = 'required_documents';

  protected $fillable = [
    'name',
    'code',
    'description',
    'applicability',
    'is_mandatory',
    'notes',
    'requires_upload',
    'status',
  ];

  protected $casts = [
    'is_mandatory' => 'boolean',
    'requires_upload' => 'boolean',
  ];

  const APPLICABILITY_ALL = 'all_surgeries';
  const APPLICABILITY_BY_TYPE = 'by_operation_type';
  const APPLICABILITY_BY_INSURANCE = 'by_insurance';
  const APPLICABILITY_CUSTOM = 'custom';

  /**
   * Get all pre-admission documents using this required document
   */
  public function preAdmissionDocuments(): HasMany
  {
    return $this->hasMany(PreAdmissionDocument::class);
  }

  /**
   * Scopes
   */
  public function scopeActive($query)
  {
    return $query->where('status', 'active');
  }

  public function scopeMandatory($query)
  {
    return $query->where('is_mandatory', true);
  }
}
