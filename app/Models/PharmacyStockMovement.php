<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyStockMovement extends Model
{
  use HasFactory;

  protected $fillable = [
    'pharmacy_item_id',
    'movement_type',
    'quantity',
    'stock_before',
    'stock_after',
    'user_id',
    'pharmacy_request_id',
    'reference',
    'notes',
  ];

  protected $casts = [
    'quantity' => 'integer',
    'stock_before' => 'integer',
    'stock_after' => 'integer',
  ];

  /**
   * Get the pharmacy item
   */
  public function pharmacyItem(): BelongsTo
  {
    return $this->belongsTo(PharmacyItem::class);
  }

  /**
   * Get the user who made the movement
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the related pharmacy request
   */
  public function pharmacyRequest(): BelongsTo
  {
    return $this->belongsTo(PharmacyRequest::class);
  }

  /**
   * Get movement type label in Spanish
   */
  public function getMovementTypeLabel(): string
  {
    return match ($this->movement_type) {
      'entry' => 'Entrada',
      'exit' => 'Salida',
      'adjustment' => 'Ajuste',
      'return' => 'Devolución',
      'expired' => 'Vencido',
      'damaged' => 'Dañado',
      default => $this->movement_type,
    };
  }
}
