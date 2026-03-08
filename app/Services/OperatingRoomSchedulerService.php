<?php

namespace App\Services;

use App\Models\Operation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class OperatingRoomSchedulerService
{
  /**
   * Calculate the end datetime from start and duration.
   */
  public function calculateEnd(Carbon $start, int $durationMinutes): Carbon
  {
    return $start->copy()->addMinutes($durationMinutes);
  }

  /**
   * Fetch operations that conflict with the provided slot including cleaning margin.
   */
  public function getConflicts(
    int $roomId,
    Carbon $start,
    Carbon $end,
    int $cleaningMarginMinutes = Operation::DEFAULT_CLEANING_MARGIN,
    ?int $excludeOperationId = null
  ): Collection {
    $startBoundary = $start->copy()->subMinutes($cleaningMarginMinutes);
    $endBoundary = $end->copy()->addMinutes($cleaningMarginMinutes);

    return Operation::query()
      ->where('operation_room_id', $roomId)
      ->agendaActive()
      ->when($excludeOperationId, fn($query) => $query->where('id', '!=', $excludeOperationId))
      ->where('scheduled_start', '<', $endBoundary)
      ->where('scheduled_end', '>', $startBoundary)
      ->orderBy('scheduled_start')
      ->get();
  }

  /**
   * Validate the slot and throw a validation error on conflict.
   */
  public function validateNoConflict(
    int $roomId,
    Carbon $start,
    Carbon $end,
    int $cleaningMarginMinutes = Operation::DEFAULT_CLEANING_MARGIN,
    ?int $excludeOperationId = null
  ): void {
    $conflicts = $this->getConflicts(
      $roomId,
      $start,
      $end,
      $cleaningMarginMinutes,
      $excludeOperationId
    );

    if ($conflicts->isNotEmpty()) {
      $first = $conflicts->first();
      $timeRange = $first->scheduled_start->format('H:i') . ' - ' . $first->scheduled_end->format('H:i');

      throw ValidationException::withMessages([
        'scheduled_start' => "La sala ya tiene una operación en conflicto ({$timeRange}). Se requieren {$cleaningMarginMinutes} minutos de limpieza entre operaciones.",
      ]);
    }
  }

  /**
   * Push conflicting and following scheduled operations forward.
   *
   * @return array<int, array<string, mixed>>
   */
  public function shiftConflictsForward(
    int $roomId,
    Carbon $insertStart,
    Carbon $insertEnd,
    int $cleaningMarginMinutes = Operation::DEFAULT_CLEANING_MARGIN,
    ?int $excludeOperationId = null,
    ?int $updatedBy = null
  ): array {
    $affected = Operation::query()
      ->where('operation_room_id', $roomId)
      ->where('status', 'scheduled')
      ->when($excludeOperationId, fn($query) => $query->where('id', '!=', $excludeOperationId))
      ->where('scheduled_end', '>', $insertStart->copy()->subMinutes($cleaningMarginMinutes))
      ->orderBy('scheduled_start')
      ->get();

    $cursor = $insertEnd->copy()->addMinutes($cleaningMarginMinutes);
    $changes = [];

    foreach ($affected as $operation) {
      $currentStart = $operation->scheduled_start->copy();
      $currentEnd = $operation->scheduled_end->copy();

      if ($currentStart->lt($cursor)) {
        $newStart = $cursor->copy();
        $newEnd = $newStart->copy()->addMinutes($operation->estimated_duration_minutes);

        $operation->update([
          'scheduled_start' => $newStart,
          'scheduled_end' => $newEnd,
          'updated_by' => $updatedBy,
        ]);

        $changes[] = [
          'operation_id' => $operation->id,
          'old_start' => $currentStart->toDateTimeString(),
          'new_start' => $newStart->toDateTimeString(),
          'old_end' => $currentEnd->toDateTimeString(),
          'new_end' => $newEnd->toDateTimeString(),
        ];

        $cursor = $newEnd->copy()->addMinutes($cleaningMarginMinutes);
        continue;
      }

      $cursor = $currentEnd->copy()->addMinutes($cleaningMarginMinutes);
    }

    return $changes;
  }

  /**
   * Try to move conflicting scheduled operations backward before an emergency slot.
   *
   * @return array<int, array<string, mixed>>
   */
  public function shiftConflictsBackward(
    int $roomId,
    Carbon $insertStart,
    Carbon $insertEnd,
    int $cleaningMarginMinutes = Operation::DEFAULT_CLEANING_MARGIN,
    ?int $excludeOperationId = null,
    ?int $updatedBy = null
  ): array {
    $conflicts = $this->getConflicts(
      $roomId,
      $insertStart,
      $insertEnd,
      $cleaningMarginMinutes,
      $excludeOperationId
    )->where('status', 'scheduled')->values();

    if ($conflicts->isEmpty()) {
      return [];
    }

    $totalOperationMinutes = $conflicts->sum('estimated_duration_minutes');
    $totalGapMinutes = max(0, ($conflicts->count() - 1) * $cleaningMarginMinutes);

    $blockEnd = $insertStart->copy()->subMinutes($cleaningMarginMinutes);
    $blockStart = $blockEnd->copy()->subMinutes($totalOperationMinutes + $totalGapMinutes);

    // Keep backward rescheduling within same day to avoid unsafe time jumps.
    if ($blockStart->toDateString() !== $insertStart->toDateString()) {
      throw ValidationException::withMessages([
        'reschedule_strategy' => 'No hay espacio suficiente para adelantar operaciones sin salir del mismo día.',
      ]);
    }

    $conflictIds = $conflicts->pluck('id')->all();
    $blockingOperationExists = Operation::query()
      ->where('operation_room_id', $roomId)
      ->agendaActive()
      ->whereNotIn('id', $conflictIds)
      ->when($excludeOperationId, fn($query) => $query->where('id', '!=', $excludeOperationId))
      ->where('scheduled_start', '<', $blockEnd)
      ->where('scheduled_end', '>', $blockStart)
      ->exists();

    if ($blockingOperationExists) {
      throw ValidationException::withMessages([
        'reschedule_strategy' => 'No se pueden adelantar operaciones porque no hay bloque libre antes de la urgencia.',
      ]);
    }

    $cursor = $blockStart->copy();
    $changes = [];

    foreach ($conflicts as $operation) {
      $currentStart = $operation->scheduled_start->copy();
      $currentEnd = $operation->scheduled_end->copy();

      $newStart = $cursor->copy();
      $newEnd = $newStart->copy()->addMinutes($operation->estimated_duration_minutes);

      $operation->update([
        'scheduled_start' => $newStart,
        'scheduled_end' => $newEnd,
        'updated_by' => $updatedBy,
      ]);

      $changes[] = [
        'operation_id' => $operation->id,
        'old_start' => $currentStart->toDateTimeString(),
        'new_start' => $newStart->toDateTimeString(),
        'old_end' => $currentEnd->toDateTimeString(),
        'new_end' => $newEnd->toDateTimeString(),
      ];

      $cursor = $newEnd->copy()->addMinutes($cleaningMarginMinutes);
    }

    return $changes;
  }
}
