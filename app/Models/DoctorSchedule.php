<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSchedule extends Model
{
  use HasFactory;

  protected $fillable = [
    'doctor_id',
    'day_of_week',
    'start_time',
    'end_time',
    'slot_duration',
    'is_active',
  ];

  protected $casts = [
    'start_time' => 'datetime:H:i',
    'end_time' => 'datetime:H:i',
    'is_active' => 'boolean',
  ];

  /**
   * Get the doctor for this schedule
   */
  public function doctor(): BelongsTo
  {
    return $this->belongsTo(User::class, 'doctor_id');
  }

  /**
   * Get day name in Spanish
   */
  public function getDayNameAttribute(): string
  {
    return match ($this->day_of_week) {
      'monday' => 'Lunes',
      'tuesday' => 'Martes',
      'wednesday' => 'Miércoles',
      'thursday' => 'Jueves',
      'friday' => 'Viernes',
      'saturday' => 'Sábado',
      'sunday' => 'Domingo',
      default => $this->day_of_week
    };
  }

  /**
   * Generate time slots for this schedule
   */
  public function generateTimeSlots(): array
  {
    $slots = [];
    $start = \Carbon\Carbon::createFromFormat('H:i:s', $this->start_time);
    $end = \Carbon\Carbon::createFromFormat('H:i:s', $this->end_time);

    while ($start->lt($end)) {
      $slots[] = $start->format('H:i');
      $start->addMinutes($this->slot_duration);
    }

    return $slots;
  }
}
