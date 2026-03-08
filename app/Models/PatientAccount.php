<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientAccount extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'patient_id',
    'balance',
    'total_charged',
    'total_paid',
    'total_credits',
    'status',
    'payment_status',
    'last_payment_date',
    'days_overdue',
    'accrued_interest',
    'interest_rate',
  ];

  protected $casts = [
    'balance' => 'decimal:2',
    'total_charged' => 'decimal:2',
    'total_paid' => 'decimal:2',
    'total_credits' => 'decimal:2',
    'accrued_interest' => 'decimal:2',
    'interest_rate' => 'decimal:2',
    'last_payment_date' => 'date',
  ];

  // Relaciones
  public function patient(): BelongsTo
  {
    return $this->belongsTo(Patient::class);
  }

  public function transactions(): HasMany
  {
    return $this->hasMany(AccountTransaction::class);
  }

  // Helpers
  public function isOverdue(): bool
  {
    return $this->payment_status === 'overdue' && $this->balance < 0;
  }

  public function getStatusLabel(): string
  {
    return match ($this->status) {
      'active' => 'Activa',
      'suspended' => 'Suspendida',
      'blocked' => 'Bloqueada',
      default => 'Desconocido'
    };
  }

  public function getPaymentStatusLabel(): string
  {
    return match ($this->payment_status) {
      'current' => 'Al día',
      'overdue' => 'Vencido',
      'in_arrears' => 'En mora',
      default => 'Desconocido'
    };
  }

  public function getDueAmount(): float
  {
    return max(0, abs($this->balance)); // Si balance es negativo, es lo que debe
  }

  public function getTotalDebt(): float
  {
    return max(0, abs($this->balance)) + $this->accrued_interest;
  }
}
