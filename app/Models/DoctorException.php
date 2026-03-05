<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorException extends Model
{
  use HasFactory;

  protected $fillable = [
    'doctor_id',
    'exception_date',
    'type',
    'reason',
    'is_all_day',
    'start_time',
    'end_time',
  ];

  protected $casts = [
    'exception_date' => 'date',
    'start_time' => 'datetime:H:i',
    'end_time' => 'datetime:H:i',
    'is_all_day' => 'boolean',
  ];

  /**
   * Get the doctor for this exception
   */
  public function doctor(): BelongsTo
  {
    return $this->belongsTo(User::class, 'doctor_id');
  }

  /**
   * Get type label in Spanish
   */
  public function getTypeLabel(): string
  {
    return match ($this->type) {
      'vacation' => 'Vacaciones',
      'sick_leave' => 'Licencia médica',
      'holiday' => 'Feriado',
      'conference' => 'Congreso/Conferencia',
      'other' => 'Otro',
      default => $this->type
    };
  }
}
