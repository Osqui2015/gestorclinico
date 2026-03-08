<?php

namespace App\Http\Controllers;

use App\Models\Ambulance;
use App\Models\EmergencyTransfer;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ParamedicController extends Controller
{
  /**
   * Dashboard de moviles y traslados.
   */
  public function dashboard(Request $request)
  {
    $ambulancesQuery = Ambulance::query();

    if ($request->filled('ambulance_status')) {
      $ambulancesQuery->where('status', $request->string('ambulance_status')->toString());
    }

    if ($request->filled('ambulance_search')) {
      $search = $request->string('ambulance_search')->toString();
      $ambulancesQuery->where(function ($query) use ($search) {
        $query->where('internal_code', 'like', "%{$search}%")
          ->orWhere('plate_number', 'like', "%{$search}%")
          ->orWhere('base_location', 'like', "%{$search}%")
          ->orWhere('brand', 'like', "%{$search}%")
          ->orWhere('model', 'like', "%{$search}%");
      });
    }

    $ambulances = $ambulancesQuery
      ->orderBy('internal_code')
      ->paginate(15, ['*'], 'ambulances_page')
      ->withQueryString();

    $transfersQuery = EmergencyTransfer::with([
      'patient:id,first_name,last_name,dni',
      'ambulance:id,internal_code,plate_number,status',
      'requester:id,name',
    ]);

    if ($request->filled('transfer_status')) {
      $transfersQuery->where('status', $request->string('transfer_status')->toString());
    }

    $transfers = $transfersQuery
      ->orderByRaw("CASE priority
        WHEN 'critical' THEN 1
        WHEN 'high' THEN 2
        WHEN 'medium' THEN 3
        ELSE 4 END")
      ->orderByDesc('requested_at')
      ->paginate(20, ['*'], 'transfers_page')
      ->withQueryString();

    $availableAmbulances = Ambulance::where('status', 'available')
      ->orderBy('internal_code')
      ->get(['id', 'internal_code', 'plate_number', 'status']);

    $patients = Patient::query()
      ->orderBy('last_name')
      ->orderBy('first_name')
      ->limit(200)
      ->get(['id', 'first_name', 'last_name', 'dni'])
      ->map(fn($patient) => [
        'id' => $patient->id,
        'full_name' => $patient->full_name,
        'dni' => $patient->dni,
      ]);

    $stats = [
      'total_ambulances' => Ambulance::count(),
      'available_ambulances' => Ambulance::where('status', 'available')->count(),
      'ambulances_in_transfer' => Ambulance::where('status', 'in_transfer')->count(),
      'ambulances_in_maintenance' => Ambulance::where('status', 'maintenance')->count(),
      'active_transfers' => EmergencyTransfer::whereIn('status', ['assigned', 'in_progress'])->count(),
      'requested_transfers' => EmergencyTransfer::where('status', 'requested')->count(),
      'completed_today' => EmergencyTransfer::whereDate('arrived_at', today())->count(),
    ];

    return Inertia::render('Paramedic/Dashboard', [
      'ambulances' => $ambulances,
      'transfers' => $transfers,
      'patients' => $patients,
      'available_ambulances' => $availableAmbulances,
      'stats' => $stats,
      'filters' => [
        'ambulance_status' => $request->input('ambulance_status'),
        'ambulance_search' => $request->input('ambulance_search'),
        'transfer_status' => $request->input('transfer_status'),
      ],
    ]);
  }

  /**
   * Registrar nuevo movil/ambulancia.
   */
  public function storeAmbulance(Request $request)
  {
    $validated = $request->validate([
      'internal_code' => 'required|string|max:100|unique:ambulances,internal_code',
      'plate_number' => 'nullable|string|max:20|unique:ambulances,plate_number',
      'brand' => 'nullable|string|max:100',
      'model' => 'nullable|string|max:100',
      'year' => 'nullable|integer|min:1990|max:2100',
      'current_mileage' => 'nullable|integer|min:0',
      'base_location' => 'nullable|string|max:255',
      'status' => 'required|in:available,in_transfer,maintenance,out_of_service',
      'last_service_at' => 'nullable|date',
      'next_service_at' => 'nullable|date',
      'notes' => 'nullable|string',
    ]);

    $validated['created_by'] = Auth::id();

    Ambulance::create($validated);

    return redirect()
      ->route('paramedic.dashboard')
      ->with('success', 'Movil registrado correctamente.');
  }

  /**
   * Actualizar estado de movil.
   */
  public function updateAmbulanceStatus(Request $request, Ambulance $ambulance)
  {
    $validated = $request->validate([
      'status' => 'required|in:available,in_transfer,maintenance,out_of_service',
      'base_location' => 'nullable|string|max:255',
      'current_mileage' => 'nullable|integer|min:0',
      'notes' => 'nullable|string',
    ]);

    $ambulance->update($validated);

    return back()->with('success', 'Estado del movil actualizado.');
  }

  /**
   * Crear solicitud de traslado.
   */
  public function storeTransfer(Request $request)
  {
    $validated = $request->validate([
      'patient_id' => 'nullable|exists:patients,id',
      'ambulance_id' => 'nullable|exists:ambulances,id',
      'origin' => 'required|string|max:255',
      'destination' => 'required|string|max:255',
      'transfer_type' => 'required|in:emergency,scheduled,interhospital,discharge',
      'priority' => 'required|in:low,medium,high,critical',
      'clinical_summary' => 'nullable|string',
    ]);

    $status = !empty($validated['ambulance_id']) ? 'assigned' : 'requested';

    $transfer = EmergencyTransfer::create([
      ...$validated,
      'requested_by' => Auth::id(),
      'status' => $status,
      'requested_at' => now(),
      'assigned_at' => !empty($validated['ambulance_id']) ? now() : null,
    ]);

    if (!empty($validated['ambulance_id'])) {
      Ambulance::where('id', $validated['ambulance_id'])
        ->update(['status' => 'in_transfer']);
    }

    return redirect()
      ->route('paramedic.dashboard')
      ->with('success', 'Traslado registrado correctamente.');
  }

  /**
   * Actualizar estado de traslado.
   */
  public function updateTransferStatus(Request $request, EmergencyTransfer $transfer)
  {
    $validated = $request->validate([
      'status' => 'required|in:requested,assigned,in_progress,completed,cancelled',
      'ambulance_id' => 'nullable|exists:ambulances,id',
      'crew_notes' => 'nullable|string',
    ]);

    $currentAmbulanceId = $transfer->ambulance_id;
    $newAmbulanceId = $validated['ambulance_id'] ?? $currentAmbulanceId;

    $data = [
      'status' => $validated['status'],
      'crew_notes' => $validated['crew_notes'] ?? $transfer->crew_notes,
      'ambulance_id' => $newAmbulanceId,
    ];

    if ($validated['status'] === 'assigned' && !$transfer->assigned_at) {
      $data['assigned_at'] = now();
    }

    if ($validated['status'] === 'in_progress' && !$transfer->departed_at) {
      $data['departed_at'] = now();
    }

    if ($validated['status'] === 'completed') {
      $data['arrived_at'] = now();
    }

    $transfer->update($data);

    if ($currentAmbulanceId && $currentAmbulanceId !== $newAmbulanceId) {
      Ambulance::where('id', $currentAmbulanceId)->update(['status' => 'available']);
    }

    if ($newAmbulanceId) {
      $ambulanceStatus = in_array($validated['status'], ['completed', 'cancelled'])
        ? 'available'
        : 'in_transfer';

      Ambulance::where('id', $newAmbulanceId)->update(['status' => $ambulanceStatus]);
    }

    return back()->with('success', 'Estado del traslado actualizado.');
  }
}
