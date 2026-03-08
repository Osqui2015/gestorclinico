<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyRequestItem extends Model
{
  use HasFactory;

  protected $fillable = [
    'pharmacy_request_id',
    'pharmacy_item_id',
    'quantity_requested',
    'quantity_delivered',
    'notes',
  ];

  protected $casts = [
    'quantity_requested' => 'integer',
    'quantity_delivered' => 'integer',
  ];

  /**
   * Get the pharmacy request
   */
  public function pharmacyRequest(): BelongsTo
  {
    return $this->belongsTo(PharmacyRequest::class);
  }

  /**
   * Get the pharmacy item
   */
  public function pharmacyItem(): BelongsTo
  {
    return $this->belongsTo(PharmacyItem::class);
  }

  /**
   * Check if item is fully delivered
   */
  public function isFullyDelivered(): bool
  {
    return $this->quantity_delivered >= $this->quantity_requested;
  }

  /**
   * Get pending quantity
   */
  public function getPendingQuantity(): int
  {
    return max(0, $this->quantity_requested - $this->quantity_delivered);
  }
}
