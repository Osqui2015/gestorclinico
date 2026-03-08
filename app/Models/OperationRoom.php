<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationRoom extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'name',
    'code',
    'display_order',
    'status',
    'notes',
  ];

  protected $casts = [
    'display_order' => 'integer',
  ];

  /**
   * Operations planned in this room.
   */
  public function operations(): HasMany
  {
    return $this->hasMany(Operation::class);
  }

  /**
   * Scope active rooms.
   */
  public function scopeActive($query)
  {
    return $query->where('status', 'active');
  }

  /**
   * Scope room order for agenda UI.
   */
  public function scopeOrdered($query)
  {
    return $query->orderBy('display_order')->orderBy('name');
  }
}
