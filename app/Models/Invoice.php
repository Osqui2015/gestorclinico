<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
  use HasFactory;

  protected $fillable = [
    'invoice_number',
    'patient_id',
    'appointment_id',
    'health_insurance_id',
    'invoice_date',
    'subtotal',
    'discount',
    'insurance_coverage',
    'total',
    'status',
    'payment_method',
    'paid_at',
    'notes',
    'created_by',
  ];

  protected $casts = [
    'invoice_date' => 'date',
    'subtotal' => 'decimal:2',
    'discount' => 'decimal:2',
    'insurance_coverage' => 'decimal:2',
    'total' => 'decimal:2',
    'paid_at' => 'datetime',
  ];

  /**
   * Get the patient for this invoice
   */
  public function patient(): BelongsTo
  {
    return $this->belongsTo(Patient::class);
  }

  /**
   * Get the appointment for this invoice
   */
  public function appointment(): BelongsTo
  {
    return $this->belongsTo(Appointment::class);
  }

  /**
   * Get the health insurance
   */
  public function healthInsurance(): BelongsTo
  {
    return $this->belongsTo(HealthInsurance::class);
  }

  /**
   * Get the user who created this invoice
   */
  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  /**
   * Get invoice items
   */
  public function items(): HasMany
  {
    return $this->hasMany(InvoiceItem::class);
  }

  /**
   * Get payments for this invoice
   */
  public function payments(): HasMany
  {
    return $this->hasMany(Payment::class);
  }

  /**
   * Get status badge color
   */
  public function getStatusColorAttribute(): string
  {
    return match ($this->status) {
      'pending' => 'yellow',
      'paid' => 'green',
      'partially_paid' => 'blue',
      'cancelled' => 'red',
      default => 'gray'
    };
  }

  /**
   * Get status label
   */
  public function getStatusLabelAttribute(): string
  {
    return match ($this->status) {
      'pending' => 'Pendiente',
      'paid' => 'Pagado',
      'partially_paid' => 'Pago Parcial',
      'cancelled' => 'Cancelado',
      default => 'Desconocido'
    };
  }

  /**
   * Get total paid amount
   */
  public function getTotalPaidAttribute(): float
  {
    return $this->payments()->sum('amount');
  }

  /**
   * Get remaining balance
   */
  public function getBalanceAttribute(): float
  {
    return $this->total - $this->getTotalPaidAttribute();
  }

  /**
   * Generate next invoice number
   */
  public static function generateInvoiceNumber(): string
  {
    $lastInvoice = static::orderBy('id', 'desc')->first();
    $nextNumber = $lastInvoice ? intval(substr($lastInvoice->invoice_number, -6)) + 1 : 1;
    return 'INV-' . date('Y') . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
  }
}
