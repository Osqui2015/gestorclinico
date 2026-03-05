<?php

namespace App\Http\Controllers;

use App\Models\HealthInsurance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HealthInsuranceController extends Controller
{
  /**
   * Display health insurances
   */
  public function index(Request $request)
  {
    $query = HealthInsurance::query()
      ->when($request->search, function ($q, $search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%");
      })
      ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->is_active))
      ->orderBy('name');

    $insurances = $query->paginate($request->per_page ?? 15);

    return Inertia::render('HealthInsurances/Index', [
      'insurances' => $insurances,
      'filters' => $request->only(['search', 'is_active']),
    ]);
  }

  /**
   * Show create form
   */
  public function create()
  {
    return Inertia::render('HealthInsurances/Create');
  }

  /**
   * Store new health insurance
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'code' => 'nullable|string|max:50|unique:health_insurances,code',
      'phone' => 'nullable|string|max:20',
      'email' => 'nullable|email|max:255',
      'copay_amount' => 'nullable|numeric|min:0',
      'copay_percentage' => 'nullable|integer|min:0|max:100',
      'requires_authorization' => 'boolean',
      'is_active' => 'boolean',
      'notes' => 'nullable|string',
    ]);

    HealthInsurance::create($validated);

    return redirect()->route('health-insurances.index')
      ->with('success', 'Obra social creada exitosamente');
  }

  /**
   * Display health insurance
   */
  public function show(HealthInsurance $healthInsurance)
  {
    $healthInsurance->load(['patients', 'invoices']);

    return Inertia::render('HealthInsurances/Show', [
      'insurance' => $healthInsurance,
    ]);
  }

  /**
   * Show edit form
   */
  public function edit(HealthInsurance $healthInsurance)
  {
    return Inertia::render('HealthInsurances/Edit', [
      'insurance' => $healthInsurance,
    ]);
  }

  /**
   * Update health insurance
   */
  public function update(Request $request, HealthInsurance $healthInsurance)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'code' => 'nullable|string|max:50|unique:health_insurances,code,' . $healthInsurance->id,
      'phone' => 'nullable|string|max:20',
      'email' => 'nullable|email|max:255',
      'copay_amount' => 'nullable|numeric|min:0',
      'copay_percentage' => 'nullable|integer|min:0|max:100',
      'requires_authorization' => 'boolean',
      'is_active' => 'boolean',
      'notes' => 'nullable|string',
    ]);

    $healthInsurance->update($validated);

    return redirect()->route('health-insurances.index')
      ->with('success', 'Obra social actualizada exitosamente');
  }

  /**
   * Delete health insurance
   */
  public function destroy(HealthInsurance $healthInsurance)
  {
    // Check if has associated patients or invoices
    if ($healthInsurance->patients()->exists() || $healthInsurance->invoices()->exists()) {
      return back()->withErrors([
        'error' => 'No se puede eliminar una obra social con pacientes o facturas asociadas'
      ]);
    }

    $healthInsurance->delete();

    return redirect()->route('health-insurances.index')
      ->with('success', 'Obra social eliminada exitosamente');
  }
}
