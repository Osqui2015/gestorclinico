<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PharmacyItem extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'name',
    'type',
    'description',
    'code',
    'laboratory',
    'unit_price',
    'current_stock',
    'minimum_stock',
    'reorder_point',
    'unit_measurement',
    'expiration_date',
    'batch_number',
    'requires_sterilization',
    'last_sterilization_date',
    'next_sterilization_date',
    'status',
    'notes',
  ];

  protected $casts = [
    'unit_price' => 'decimal:2',
    'current_stock' => 'integer',
    'minimum_stock' => 'integer',
    'reorder_point' => 'integer',
    'expiration_date' => 'date',
    'last_sterilization_date' => 'date',
    'next_sterilization_date' => 'date',
    'requires_sterilization' => 'boolean',
  ];

  /**
   * Get stock movements for this item
   */
  public function stockMovements(): HasMany
  {
    return $this->hasMany(PharmacyStockMovement::class);
  }

  /**
   * Get request items for this pharmacy item
   */
  public function requestItems(): HasMany
  {
    return $this->hasMany(PharmacyRequestItem::class);
  }

  /**
   * Get operation pharmacy requirements linked to this item.
   */
  public function operationRequirements(): HasMany
  {
    return $this->hasMany(OperationPharmacyItem::class);
  }

  /**
   * Check if item is in low stock
   */
  public function isLowStock(): bool
  {
    return $this->current_stock <= $this->minimum_stock;
  }

  /**
   * Check if item is expiring soon (within 30 days)
   */
  public function isExpiringSoon(): bool
  {
    if (!$this->expiration_date) {
      return false;
    }
    return $this->expiration_date->lte(now()->addDays(30));
  }

  /**
   * Check if item is expired
   */
  public function isExpired(): bool
  {
    if (!$this->expiration_date) {
      return false;
    }
    return $this->expiration_date->lt(now());
  }

  /**
   * Check if sterilization is due (within 7 days)
   */
  public function isSterilizationDue(): bool
  {
    if (!$this->requires_sterilization || !$this->next_sterilization_date) {
      return false;
    }
    return $this->next_sterilization_date->lte(now()->addDays(7));
  }

  /**
   * Scope to get items with low stock
   */
  public function scopeLowStock($query)
  {
    return $query->whereRaw('current_stock <= minimum_stock');
  }

  /**
   * Scope to get items expiring soon
   */
  public function scopeExpiringSoon($query)
  {
    return $query->where('expiration_date', '<=', now()->addDays(30))
      ->where('expiration_date', '>', now());
  }

  /**
   * Scope to get expired items
   */
  public function scopeExpired($query)
  {
    return $query->where('expiration_date', '<', now());
  }

  /**
   * Scope to get items needing sterilization
   */
  public function scopeSterilizationDue($query)
  {
    return $query->where('requires_sterilization', true)
      ->where('next_sterilization_date', '<=', now()->addDays(7));
  }

  /**
   * Get type label in Spanish
   */
  public function getTypeLabel(): string
  {
    return match ($this->type) {
      'medication' => 'Medicamento',
      'instrument' => 'Instrumento',
      'supply' => 'Insumo',
      default => $this->type,
    };
  }

  /**
   * Get status label in Spanish
   */
  public function getStatusLabel(): string
  {
    return match ($this->status) {
      'active' => 'Activo',
      'inactive' => 'Inactivo',
      'discontinued' => 'Discontinuado',
      default => $this->status,
    };
  }
}
