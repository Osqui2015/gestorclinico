<?php

namespace App\Http\Controllers;

use App\Models\OperationRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OperationRoomController extends Controller
{
  /**
   * Show operating room settings and current capacity.
   */
  public function settings()
  {
    $rooms = OperationRoom::query()
      ->ordered()
      ->withCount([
        'operations as upcoming_operations_count' => function ($query) {
          $query->agendaActive()->where('scheduled_start', '>=', now());
        },
      ])
      ->get();

    return Inertia::render('Operations/RoomsSettings', [
      'rooms' => $rooms,
      'activeRoomsCount' => $rooms->where('status', 'active')->count(),
      'totalRoomsCount' => $rooms->count(),
      'permissions' => [
        'canUpdateCapacity' => Auth::user()?->role === 'admin',
      ],
    ]);
  }

  /**
   * Update desired amount of active operating rooms.
   */
  public function updateCapacity(Request $request)
  {
    $validated = $request->validate([
      'room_count' => 'required|integer|min:1|max:30',
    ]);

    $desiredCount = (int) $validated['room_count'];
    $blockedRooms = [];

    DB::transaction(function () use ($desiredCount, &$blockedRooms) {
      $rooms = OperationRoom::query()->ordered()->get();
      $existingCount = $rooms->count();

      if ($existingCount < $desiredCount) {
        for ($i = $existingCount + 1; $i <= $desiredCount; $i++) {
          OperationRoom::create([
            'name' => 'Sala ' . $i,
            'code' => 'Q-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            'display_order' => $i,
            'status' => 'active',
          ]);
        }
      }

      $rooms = OperationRoom::query()->ordered()->get()->values();

      foreach ($rooms as $index => $room) {
        $shouldBeActive = $index < $desiredCount;

        if ($shouldBeActive) {
          if ($room->status !== 'active') {
            $room->update(['status' => 'active']);
          }
          continue;
        }

        $hasUpcomingOperations = $room->operations()
          ->agendaActive()
          ->where('scheduled_start', '>=', now())
          ->exists();

        if ($hasUpcomingOperations) {
          $blockedRooms[] = $room->name;
          continue;
        }

        if ($room->status !== 'inactive') {
          $room->update(['status' => 'inactive']);
        }
      }
    });

    if (!empty($blockedRooms)) {
      return back()->with('warning', 'No se pudieron desactivar salas con operaciones futuras: ' . implode(', ', $blockedRooms));
    }

    return back()->with('success', 'Cantidad de salas actualizada correctamente.');
  }

  /**
   * Update room status/details.
   */
  public function update(Request $request, OperationRoom $room)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'status' => 'required|in:active,maintenance,inactive',
      'notes' => 'nullable|string',
    ]);

    if ($validated['status'] === 'inactive') {
      $hasUpcomingOperations = $room->operations()
        ->agendaActive()
        ->where('scheduled_start', '>=', now())
        ->exists();

      if ($hasUpcomingOperations) {
        return back()->withErrors([
          'status' => 'No se puede desactivar una sala que tiene operaciones futuras.',
        ]);
      }
    }

    $room->update($validated);

    return back()->with('success', 'Sala actualizada correctamente.');
  }
}
