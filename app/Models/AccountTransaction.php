<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransaction extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'patient_account_id',
    'created_by',
    'type',
    'concept',
    'description',
    'amount',
    'balance_after',
    'reference_type',
    'reference_id',
    'voucher_number',
    'transaction_date',
    'payment_method',
    'notes',
  ];

  protected $casts = [
    'amount' => 'decimal:2',
    'balance_after' => 'decimal:2',
    'transaction_date' => 'date',
  ];

  // Relaciones
  public function account(): BelongsTo
  {
    return $this->belongsTo(PatientAccount::class, 'patient_account_id');
  }

  public function createdBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  // Helpers
  public function getTypeLabel(): string
  {
    return match ($this->type) {
      'charge' => 'Cobro',
      'payment' => 'Pago',
      'credit' => 'Crédito',
      'write_off' => 'Condonación',
      'interest' => 'Intereses',
      'adjustment' => 'Ajuste',
      'refund' => 'Devolución',
      default => 'Desconocido'
    };
  }

  public function getPaymentMethodLabel(): string
  {
    return match ($this->payment_method) {
      'cash' => 'Efectivo',
      'check' => 'Cheque',
      'transfer' => 'Transferencia',
      'credit_card' => 'Tarjeta de crédito',
      'debit_card' => 'Tarjeta de débito',
      'promissory_note' => 'Pagaré',
      'credit' => 'Crédito',
      'insurance' => 'Obra social',
      'other' => 'Otro',
      default => 'N/A'
    };
  }

  public function getIcon(): string
  {
    return match ($this->type) {
      'charge' => '📤',
      'payment' => '✅',
      'credit' => '💳',
      'write_off' => '🗑',
      'interest' => '📈',
      'adjustment' => '⚙️',
      'refund' => '↩️',
      default => '📝'
    };
  }
}
