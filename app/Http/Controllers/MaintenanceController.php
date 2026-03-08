<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceOrder;
use App\Models\MedicalEquipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MaintenanceController extends Controller
{
  /**
   * Dashboard de mantenimiento y equipos medicos.
   */
  public function index(Request $request)
  {
    $equipmentQuery = MedicalEquipment::query()
      ->withCount([
        'openOrders as pending_orders_count',
      ]);

    if ($request->filled('status')) {
      $equipmentQuery->where('status', $request->string('status')->toString());
    }

    if ($request->filled('category')) {
      $equipmentQuery->where('category', $request->string('category')->toString());
    }

    if ($request->filled('search')) {
      $search = $request->string('search')->toString();
      $equipmentQuery->where(function ($query) use ($search) {
        $query->where('name', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('location', 'like', "%{$search}%")
          ->orWhere('brand', 'like', "%{$search}%")
          ->orWhere('model', 'like', "%{$search}%");
      });
    }

    $equipments = $equipmentQuery
      ->orderBy('name')
      ->paginate(20, ['*'], 'equipments_page')
      ->withQueryString();

    $ordersQuery = MaintenanceOrder::with([
      'equipment:id,name,code,location,status',
      'reporter:id,name',
      'assignee:id,name',
    ]);

    if ($request->filled('order_status')) {
      $ordersQuery->where('status', $request->string('order_status')->toString());
    }

    $orders = $ordersQuery
      ->orderByRaw("CASE priority
        WHEN 'critical' THEN 1
        WHEN 'high' THEN 2
        WHEN 'medium' THEN 3
        ELSE 4 END")
      ->orderByDesc('reported_at')
      ->limit(30)
      ->get();

    $technicians = User::whereIn('role', ['maintenance', 'admin'])
      ->orderBy('name')
      ->get(['id', 'name', 'role']);

    $stats = [
      'total_equipments' => MedicalEquipment::count(),
      'operational' => MedicalEquipment::where('status', 'operational')->count(),
      'in_maintenance' => MedicalEquipment::where('status', 'in_maintenance')->count(),
      'maintenance_required' => MedicalEquipment::where('status', 'maintenance_required')->count(),
      'open_orders' => MaintenanceOrder::whereIn('status', ['open', 'in_progress', 'on_hold'])->count(),
      'critical_orders' => MaintenanceOrder::where('priority', 'critical')
        ->whereIn('status', ['open', 'in_progress', 'on_hold'])
        ->count(),
    ];

    return Inertia::render('Maintenance/Index', [
      'equipments' => $equipments,
      'orders' => $orders,
      'technicians' => $technicians,
      'stats' => $stats,
      'filters' => [
        'status' => $request->input('status'),
        'category' => $request->input('category'),
        'search' => $request->input('search'),
        'order_status' => $request->input('order_status'),
      ],
    ]);
  }

  /**
   * Registrar nuevo equipo medico.
   */
  public function storeEquipment(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'code' => 'nullable|string|max:100|unique:medical_equipments,code',
      'category' => 'required|in:monitoring,imaging,life_support,laboratory,surgical,other',
      'brand' => 'nullable|string|max:120',
      'model' => 'nullable|string|max:120',
      'serial_number' => 'nullable|string|max:150',
      'location' => 'nullable|string|max:255',
      'status' => 'required|in:operational,maintenance_required,in_maintenance,out_of_service',
      'next_maintenance_at' => 'nullable|date',
      'notes' => 'nullable|string',
    ]);

    $validated['created_by'] = Auth::id();

    MedicalEquipment::create($validated);

    return redirect()
      ->route('maintenance.index')
      ->with('success', 'Equipo medico registrado correctamente.');
  }

  /**
   * Crear orden de mantenimiento.
   */
  public function storeOrder(Request $request)
  {
    $validated = $request->validate([
      'medical_equipment_id' => 'required|exists:medical_equipments,id',
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'priority' => 'required|in:low,medium,high,critical',
      'assigned_to' => 'nullable|exists:users,id',
    ]);

    $order = MaintenanceOrder::create([
      ...$validated,
      'reported_by' => Auth::id(),
      'status' => 'open',
      'reported_at' => now(),
    ]);

    $equipment = MedicalEquipment::find($validated['medical_equipment_id']);
    if ($equipment && $equipment->status === 'operational') {
      $equipment->update([
        'status' => 'maintenance_required',
      ]);
    }

    return redirect()
      ->route('maintenance.index')
      ->with('success', 'Orden de mantenimiento creada correctamente.');
  }

  /**
   * Actualizar estado de orden de mantenimiento.
   */
  public function updateOrderStatus(Request $request, MaintenanceOrder $order)
  {
    $validated = $request->validate([
      'status' => 'required|in:open,in_progress,on_hold,completed,cancelled',
      'assigned_to' => 'nullable|exists:users,id',
      'resolution_notes' => 'nullable|string',
      'cost' => 'nullable|numeric|min:0',
    ]);

    $data = [
      'status' => $validated['status'],
      'assigned_to' => $validated['assigned_to'] ?? $order->assigned_to,
      'resolution_notes' => $validated['resolution_notes'] ?? $order->resolution_notes,
      'cost' => $validated['cost'] ?? $order->cost,
    ];

    if ($validated['status'] === 'in_progress' && !$order->started_at) {
      $data['started_at'] = now();
    }

    if ($validated['status'] === 'completed') {
      $data['completed_at'] = now();
    }

    $order->update($data);

    $equipment = $order->equipment;
    if ($equipment) {
      if ($validated['status'] === 'in_progress') {
        $equipment->update(['status' => 'in_maintenance']);
      }

      if ($validated['status'] === 'completed') {
        $equipment->update([
          'status' => 'operational',
          'last_maintenance_at' => now(),
          'next_maintenance_at' => now()->addDays(90),
        ]);
      }

      if ($validated['status'] === 'cancelled' && $equipment->status === 'in_maintenance') {
        $equipment->update(['status' => 'maintenance_required']);
      }
    }

    return back()->with('success', 'Estado de la orden actualizado.');
  }

  /**
   * Actualizar estado de un equipo.
   */
  public function updateEquipmentStatus(Request $request, MedicalEquipment $equipment)
  {
    $validated = $request->validate([
      'status' => 'required|in:operational,maintenance_required,in_maintenance,out_of_service',
      'location' => 'nullable|string|max:255',
      'next_maintenance_at' => 'nullable|date',
      'notes' => 'nullable|string',
    ]);

    $equipment->update($validated);

    return back()->with('success', 'Estado del equipo actualizado.');
  }
}
