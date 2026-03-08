<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationPharmacyItem extends Model
{
  use HasFactory;

  protected $fillable = [
    'operation_id',
    'pharmacy_item_id',
    'requested_item_name',
    'quantity_required',
    'unit_measurement',
    'picked_up',
    'picked_up_at',
    'notes',
  ];

  protected $casts = [
    'quantity_required' => 'integer',
    'picked_up' => 'boolean',
    'picked_up_at' => 'datetime',
  ];

  /**
   * Parent operation.
   */
  public function operation(): BelongsTo
  {
    return $this->belongsTo(Operation::class);
  }

  /**
   * Linked pharmacy catalog item.
   */
  public function pharmacyItem(): BelongsTo
  {
    return $this->belongsTo(PharmacyItem::class);
  }
}
