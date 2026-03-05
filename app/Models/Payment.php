<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
  use HasFactory;

  protected $fillable = [
    'invoice_id',
    'amount',
    'payment_method',
    'reference_number',
    'payment_date',
    'notes',
    'received_by',
  ];

  protected $casts = [
    'amount' => 'decimal:2',
    'payment_date' => 'date',
  ];

  /**
   * Get the invoice for this payment
   */
  public function invoice(): BelongsTo
  {
    return $this->belongsTo(Invoice::class);
  }

  /**
   * Get the user who received this payment
   */
  public function receiver(): BelongsTo
  {
    return $this->belongsTo(User::class, 'received_by');
  }

  /**
   * Get payment method label
   */
  public function getPaymentMethodLabelAttribute(): string
  {
    return match ($this->payment_method) {
      'cash' => 'Efectivo',
      'card' => 'Tarjeta',
      'transfer' => 'Transferencia',
      'check' => 'Cheque',
      'other' => 'Otro',
      default => $this->payment_method
    };
  }
}
