<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HealthInsurance extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'code',
    'phone',
    'email',
    'copay_amount',
    'copay_percentage',
    'requires_authorization',
    'is_active',
    'notes',
  ];

  protected $casts = [
    'copay_amount' => 'decimal:2',
    'copay_percentage' => 'integer',
    'requires_authorization' => 'boolean',
    'is_active' => 'boolean',
  ];

  /**
   * Get patients with this insurance
   */
  public function patients(): BelongsToMany
  {
    return $this->belongsToMany(Patient::class, 'patient_insurance')
      ->withPivot('member_number', 'valid_from', 'valid_until', 'is_primary')
      ->withTimestamps();
  }

  /**
   * Get invoices for this insurance
   */
  public function invoices()
  {
    return $this->hasMany(Invoice::class);
  }
}
