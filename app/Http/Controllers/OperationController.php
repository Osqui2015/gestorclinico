<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\OperationRoom;
use App\Models\Patient;
use App\Models\PharmacyItem;
use App\Models\PreAdmission;
use App\Models\User;
use App\Services\OperatingRoomSchedulerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OperationController extends Controller
{
  public function __construct(private readonly OperatingRoomSchedulerService $scheduler) {}

  /**
   * Agenda view for operating rooms.
   */
  public function index(Request $request)
  {
    $date = $request->input('date', now()->toDateString());
    $selectedDate = Carbon::parse($date)->startOfDay();
    $dayStart = $selectedDate->copy()->startOfDay();
    $dayEnd = $selectedDate->copy()->endOfDay();
    $status = $request->input('status');
    $roomId = $request->input('room_id');

    $rooms = OperationRoom::query()
      ->ordered()
      ->whereIn('status', ['active', 'maintenance'])
      ->get();

    $operationsQuery = Operation::query()
      ->with([
        'room:id,name,code,status',
        'doctor:id,name',
        'patient:id,first_name,last_name,dni',
        'pharmacyItems.pharmacyItem:id,name,code,unit_measurement',
      ])
      ->where('scheduled_start', '<', $dayEnd)
      ->where('scheduled_end', '>', $dayStart)
      ->orderBy('scheduled_start');

    if ($roomId) {
      $operationsQuery->where('operation_room_id', $roomId);
    }

    if ($status) {
      $operationsQuery->where('status', $status);
    }

    $operations = $operationsQuery->get();

    return Inertia::render('Operations/Index', [
      'rooms' => $rooms,
      'operations' => $operations,
      'availability' => $this->buildAvailability($rooms, $operations, $selectedDate),
      'filters' => [
        'date' => $selectedDate->toDateString(),
        'room_id' => $roomId,
        'status' => $status,
      ],
      'permissions' => [
        'canManage' => $this->canManage(Auth::user()),
        'canCreate' => in_array(Auth::user()?->role, ['doctor', 'admin', 'operating_room_manager'], true),
      ],
    ]);
  }

  /**
   * Show operation creation form.
   */
  public function create()
  {
    $user = Auth::user();

    $rooms = OperationRoom::query()
      ->active()
      ->ordered()
      ->get(['id', 'name', 'code']);

    $doctors = User::query()
      ->where('role', 'doctor')
      ->orderBy('name')
      ->get(['id', 'name', 'specialty']);

    $patients = Patient::query()
      ->orderBy('last_name')
      ->orderBy('first_name')
      ->limit(300)
      ->get(['id', 'first_name', 'last_name', 'dni']);

    $pharmacyItems = PharmacyItem::query()
      ->where('status', 'active')
      ->orderBy('name')
      ->get(['id', 'name', 'code', 'unit_measurement', 'current_stock']);

    return Inertia::render('Operations/Create', [
      'rooms' => $rooms,
      'doctors' => $doctors,
      'patients' => $patients,
      'pharmacyItems' => $pharmacyItems,
      'defaults' => [
        'doctor_id' => $user?->role === 'doctor' ? $user->id : null,
        'date' => now()->toDateString(),
        'cleaning_margin_minutes' => Operation::DEFAULT_CLEANING_MARGIN,
      ],
      'permissions' => [
        'canManage' => $this->canManage($user),
      ],
    ]);
  }

  /**
   * Store a newly scheduled operation.
   */
  public function store(Request $request)
  {
    $validated = $this->validateOperationPayload($request);
    $user = Auth::user();

    if ($user->role === 'doctor') {
      $validated['doctor_id'] = $user->id;
    }

    $doctor = User::find($validated['doctor_id']);
    if (!$doctor || $doctor->role !== 'doctor') {
      return back()->withErrors(['doctor_id' => 'El profesional seleccionado no es un médico válido.']);
    }

    $room = OperationRoom::findOrFail($validated['operation_room_id']);
    if ($room->status !== 'active') {
      return back()->withErrors(['operation_room_id' => 'Solo se pueden programar operaciones en salas habilitadas.']);
    }

    $start = Carbon::parse($validated['scheduled_start']);
    $end = $this->scheduler->calculateEnd($start, (int) $validated['estimated_duration_minutes']);
    $strategy = $validated['reschedule_strategy'] ?? 'none';
    $movedOperations = [];

    DB::transaction(function () use ($validated, $user, $start, $end, $strategy, &$movedOperations) {
      if (($validated['urgency'] ?? 'scheduled') === 'emergency') {
        if ($strategy === 'forward') {
          $movedOperations = $this->scheduler->shiftConflictsForward(
            (int) $validated['operation_room_id'],
            $start,
            $end,
            Operation::DEFAULT_CLEANING_MARGIN,
            null,
            $user->id
          );
        } elseif ($strategy === 'backward') {
          $movedOperations = $this->scheduler->shiftConflictsBackward(
            (int) $validated['operation_room_id'],
            $start,
            $end,
            Operation::DEFAULT_CLEANING_MARGIN,
            null,
            $user->id
          );
        } else {
          $this->scheduler->validateNoConflict(
            (int) $validated['operation_room_id'],
            $start,
            $end,
            Operation::DEFAULT_CLEANING_MARGIN
          );
        }
      } else {
        $this->scheduler->validateNoConflict(
          (int) $validated['operation_room_id'],
          $start,
          $end,
          Operation::DEFAULT_CLEANING_MARGIN
        );
      }

      $operation = Operation::create([
        'operation_room_id' => $validated['operation_room_id'],
        'doctor_id' => $validated['doctor_id'],
        'patient_id' => $validated['patient_id'],
        'operation_type' => $validated['operation_type'],
        'scheduled_start' => $start,
        'scheduled_end' => $end,
        'estimated_duration_minutes' => $validated['estimated_duration_minutes'],
        'cleaning_margin_minutes' => Operation::DEFAULT_CLEANING_MARGIN,
        'urgency' => $validated['urgency'] ?? 'scheduled',
        'status' => 'scheduled',
        'clinical_notes' => $validated['clinical_notes'] ?? null,
        'pharmacy_notes' => $validated['pharmacy_notes'] ?? null,
        'created_by' => $user->id,
        'updated_by' => $user->id,
      ]);

      $this->syncPharmacyItems($operation, $validated['pharmacy_items'] ?? []);

      // Ensure each operation has a pre-admission workflow record.
      $operation->preAdmission()->updateOrCreate(
        [],
        [
          'patient_id' => $operation->patient_id,
          'status' => PreAdmission::STATUS_PENDING_ASSIGNMENT,
        ]
      );
    });

    $message = 'Operación programada correctamente.';
    if (!empty($movedOperations)) {
      $message .= ' Se reprogramaron ' . count($movedOperations) . ' operación(es) por urgencia.';
    }

    return redirect()->route('operations.index', ['date' => $start->toDateString()])
      ->with('success', $message);
  }

  /**
   * Show operation edit form.
   */
  public function edit(Operation $operation)
  {
    $user = Auth::user();
    $this->ensureCanEdit($user, $operation);

    $rooms = OperationRoom::query()
      ->whereIn('status', ['active', 'maintenance'])
      ->ordered()
      ->get(['id', 'name', 'code', 'status']);

    $doctors = User::query()
      ->where('role', 'doctor')
      ->orderBy('name')
      ->get(['id', 'name', 'specialty']);

    $patients = Patient::query()
      ->orderBy('last_name')
      ->orderBy('first_name')
      ->limit(300)
      ->get(['id', 'first_name', 'last_name', 'dni']);

    $pharmacyItems = PharmacyItem::query()
      ->where('status', 'active')
      ->orderBy('name')
      ->get(['id', 'name', 'code', 'unit_measurement', 'current_stock']);

    $operation->load(['pharmacyItems.pharmacyItem']);

    return Inertia::render('Operations/Edit', [
      'operation' => $operation,
      'rooms' => $rooms,
      'doctors' => $doctors,
      'patients' => $patients,
      'pharmacyItems' => $pharmacyItems,
      'permissions' => [
        'canManage' => $this->canManage($user),
      ],
    ]);
  }

  /**
   * Update an existing operation.
   */
  public function update(Request $request, Operation $operation)
  {
    $user = Auth::user();
    $this->ensureCanEdit($user, $operation);

    if (in_array($operation->status, ['completed', 'cancelled'], true)) {
      return back()->withErrors(['status' => 'No se puede editar una operación finalizada o cancelada.']);
    }

    $validated = $this->validateOperationPayload($request, true);

    if ($user->role === 'doctor') {
      $validated['doctor_id'] = $user->id;
    }

    $doctor = User::find($validated['doctor_id']);
    if (!$doctor || $doctor->role !== 'doctor') {
      return back()->withErrors(['doctor_id' => 'El profesional seleccionado no es un médico válido.']);
    }

    $room = OperationRoom::findOrFail($validated['operation_room_id']);
    if ($room->status !== 'active' && (int) $room->id !== (int) $operation->operation_room_id) {
      return back()->withErrors(['operation_room_id' => 'Solo se pueden reprogramar a salas habilitadas.']);
    }

    $start = Carbon::parse($validated['scheduled_start']);
    $end = $this->scheduler->calculateEnd($start, (int) $validated['estimated_duration_minutes']);
    $strategy = $validated['reschedule_strategy'] ?? 'none';
    $movedOperations = [];

    DB::transaction(function () use ($validated, $operation, $user, $start, $end, $strategy, &$movedOperations) {
      if (($validated['urgency'] ?? 'scheduled') === 'emergency') {
        if ($strategy === 'forward') {
          $movedOperations = $this->scheduler->shiftConflictsForward(
            (int) $validated['operation_room_id'],
            $start,
            $end,
            Operation::DEFAULT_CLEANING_MARGIN,
            $operation->id,
            $user->id
          );
        } elseif ($strategy === 'backward') {
          $movedOperations = $this->scheduler->shiftConflictsBackward(
            (int) $validated['operation_room_id'],
            $start,
            $end,
            Operation::DEFAULT_CLEANING_MARGIN,
            $operation->id,
            $user->id
          );
        } else {
          $this->scheduler->validateNoConflict(
            (int) $validated['operation_room_id'],
            $start,
            $end,
            Operation::DEFAULT_CLEANING_MARGIN,
            $operation->id
          );
        }
      } else {
        $this->scheduler->validateNoConflict(
          (int) $validated['operation_room_id'],
          $start,
          $end,
          Operation::DEFAULT_CLEANING_MARGIN,
          $operation->id
        );
      }

      $operation->update([
        'operation_room_id' => $validated['operation_room_id'],
        'doctor_id' => $validated['doctor_id'],
        'patient_id' => $validated['patient_id'],
        'operation_type' => $validated['operation_type'],
        'scheduled_start' => $start,
        'scheduled_end' => $end,
        'estimated_duration_minutes' => $validated['estimated_duration_minutes'],
        'cleaning_margin_minutes' => Operation::DEFAULT_CLEANING_MARGIN,
        'urgency' => $validated['urgency'] ?? $operation->urgency,
        'clinical_notes' => $validated['clinical_notes'] ?? null,
        'pharmacy_notes' => $validated['pharmacy_notes'] ?? null,
        'updated_by' => $user->id,
      ]);

      $this->syncPharmacyItems($operation, $validated['pharmacy_items'] ?? []);

      // Keep patient linkage updated even if the operation was reassigned.
      $operation->preAdmission()->updateOrCreate(
        [],
        [
          'patient_id' => $operation->patient_id,
        ]
      );
    });

    $message = 'Operación actualizada correctamente.';
    if (!empty($movedOperations)) {
      $message .= ' Se reprogramaron ' . count($movedOperations) . ' operación(es).';
    }

    return redirect()->route('operations.index', ['date' => $start->toDateString()])
      ->with('success', $message);
  }

  /**
   * Cancel an operation.
   */
  public function cancel(Request $request, Operation $operation)
  {
    $user = Auth::user();
    $this->ensureCanEdit($user, $operation);

    if ($operation->status === 'completed') {
      return back()->withErrors(['status' => 'No se puede cancelar una operación ya completada.']);
    }

    $validated = $request->validate([
      'cancellation_reason' => 'required|string|max:1000',
    ]);

    $operation->update([
      'status' => 'cancelled',
      'cancellation_reason' => $validated['cancellation_reason'],
      'cancelled_at' => now(),
      'cancelled_by' => $user->id,
      'updated_by' => $user->id,
    ]);

    return back()->with('success', 'Operación cancelada correctamente.');
  }

  /**
   * Quick status updates for operation manager/admin.
   */
  public function quickStatus(Request $request, Operation $operation)
  {
    $user = Auth::user();

    if (!$this->canManage($user)) {
      abort(403, 'Solo administración o encargado de quirófano puede cambiar estados rápido.');
    }

    $validated = $request->validate([
      'status' => 'required|in:scheduled,in_progress,completed',
    ]);

    if ($operation->status === 'cancelled') {
      return back()->withErrors(['status' => 'No se puede cambiar el estado de una operación cancelada.']);
    }

    $operation->update([
      'status' => $validated['status'],
      'updated_by' => $user->id,
    ]);

    return back()->with('success', 'Estado de operación actualizado.');
  }

  /**
   * Soft-delete operation (only for manager/admin and cancelled records).
   */
  public function destroy(Operation $operation)
  {
    $user = Auth::user();

    if (!$this->canManage($user)) {
      abort(403, 'No tienes permiso para eliminar operaciones.');
    }

    if ($operation->status !== 'cancelled') {
      return back()->withErrors(['status' => 'Solo se pueden eliminar operaciones previamente canceladas.']);
    }

    $operation->delete();

    return back()->with('success', 'Operación eliminada.');
  }

  /**
   * Validate operation payload.
   */
  private function validateOperationPayload(Request $request, bool $isUpdate = false): array
  {
    $rules = [
      'operation_room_id' => 'required|exists:operation_rooms,id',
      'doctor_id' => 'required|exists:users,id',
      'patient_id' => 'required|exists:patients,id',
      'operation_type' => 'required|string|max:255',
      'scheduled_start' => $isUpdate
        ? 'required|date'
        : 'required|date|after_or_equal:now',
      'estimated_duration_minutes' => 'required|integer|min:15|max:720',
      'urgency' => 'required|in:scheduled,urgent,emergency',
      'reschedule_strategy' => 'nullable|in:none,forward,backward',
      'clinical_notes' => 'nullable|string',
      'pharmacy_notes' => 'nullable|string',
      'pharmacy_items' => 'nullable|array',
      'pharmacy_items.*.pharmacy_item_id' => 'nullable|exists:pharmacy_items,id',
      'pharmacy_items.*.requested_item_name' => 'nullable|string|max:255',
      'pharmacy_items.*.quantity_required' => 'required_with:pharmacy_items.*.pharmacy_item_id,pharmacy_items.*.requested_item_name|integer|min:1|max:9999',
      'pharmacy_items.*.unit_measurement' => 'nullable|string|max:100',
      'pharmacy_items.*.notes' => 'nullable|string|max:1000',
    ];

    return $request->validate($rules);
  }

  /**
   * Persist operation pharmacy requirements.
   */
  private function syncPharmacyItems(Operation $operation, array $items): void
  {
    $operation->pharmacyItems()->delete();

    foreach ($items as $item) {
      $hasCatalogItem = !empty($item['pharmacy_item_id']);
      $hasManualName = !empty($item['requested_item_name']);

      if (!$hasCatalogItem && !$hasManualName) {
        continue;
      }

      $operation->pharmacyItems()->create([
        'pharmacy_item_id' => $item['pharmacy_item_id'] ?? null,
        'requested_item_name' => $item['requested_item_name'] ?? null,
        'quantity_required' => (int) ($item['quantity_required'] ?? 1),
        'unit_measurement' => $item['unit_measurement'] ?? null,
        'notes' => $item['notes'] ?? null,
      ]);
    }
  }

  /**
   * Build free windows by room for selected date.
   */
  private function buildAvailability(Collection $rooms, Collection $operations, Carbon $date): array
  {
    $dayStart = $date->copy()->setTime(7, 0);
    $dayEnd = $date->copy()->setTime(22, 0);

    return $rooms->map(function ($room) use ($operations, $dayStart, $dayEnd) {
      $roomOperations = $operations
        ->where('operation_room_id', $room->id)
        ->whereIn('status', ['scheduled', 'in_progress'])
        ->sortBy('scheduled_start')
        ->values();

      $cursor = $dayStart->copy();
      $slots = [];

      foreach ($roomOperations as $operation) {
        $operationStart = Carbon::parse($operation->scheduled_start);
        $margin = (int) ($operation->cleaning_margin_minutes ?? Operation::DEFAULT_CLEANING_MARGIN);
        $freeUntil = $operationStart->copy()->subMinutes($margin);

        if ($freeUntil->gt($cursor)) {
          $slots[] = [
            'start' => $cursor->format('H:i'),
            'end' => $freeUntil->format('H:i'),
            'minutes' => $cursor->diffInMinutes($freeUntil),
          ];
        }

        $occupiedUntil = Carbon::parse($operation->scheduled_end)->addMinutes($margin);
        if ($occupiedUntil->gt($cursor)) {
          $cursor = $occupiedUntil;
        }
      }

      if ($cursor->lt($dayEnd)) {
        $slots[] = [
          'start' => $cursor->format('H:i'),
          'end' => $dayEnd->format('H:i'),
          'minutes' => $cursor->diffInMinutes($dayEnd),
        ];
      }

      return [
        'room_id' => $room->id,
        'room_name' => $room->name,
        'room_code' => $room->code,
        'room_status' => $room->status,
        'slots' => $slots,
        'total_free_minutes' => collect($slots)->sum('minutes'),
      ];
    })->all();
  }

  /**
   * Role helper for manager-level permissions.
   */
  private function canManage(?User $user): bool
  {
    if (!$user) {
      return false;
    }

    return in_array($user->role, ['admin', 'operating_room_manager'], true);
  }

  /**
   * Ensure current user can edit/cancel operation.
   */
  private function ensureCanEdit(?User $user, Operation $operation): void
  {
    if (!$user) {
      abort(403, 'No autenticado.');
    }

    if ($this->canManage($user)) {
      return;
    }

    if ($user->role === 'doctor' && (int) $operation->doctor_id === (int) $user->id) {
      return;
    }

    abort(403, 'No tienes permiso para modificar esta operación.');
  }
}
