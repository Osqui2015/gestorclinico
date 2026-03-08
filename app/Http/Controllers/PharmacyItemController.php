<?php

namespace App\Http\Controllers;

use App\Models\PharmacyItem;
use App\Models\PharmacyStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PharmacyItemController extends Controller
{
  /**
   * Display a listing of pharmacy items
   */
  public function index(Request $request)
  {
    $query = PharmacyItem::query();

    // Search
    if ($request->has('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('laboratory', 'like', "%{$search}%");
      });
    }

    // Filter by type
    if ($request->has('type') && $request->type !== '') {
      $query->where('type', $request->type);
    }

    // Filter by status
    if ($request->has('status') && $request->status !== '') {
      $query->where('status', $request->status);
    } else {
      $query->where('status', 'active');
    }

    // Filter by alerts
    if ($request->has('alert')) {
      switch ($request->alert) {
        case 'low_stock':
          $query->lowStock();
          break;
        case 'expiring_soon':
          $query->expiringSoon();
          break;
        case 'expired':
          $query->expired();
          break;
        case 'sterilization_due':
          $query->sterilizationDue();
          break;
      }
    }

    $items = $query->orderBy('name', 'asc')->paginate(20);

    return Inertia::render('Pharmacy/Items/Index', [
      'items' => $items,
      'filters' => $request->only(['search', 'type', 'status', 'alert']),
    ]);
  }

  /**
   * Show the form for creating a new pharmacy item
   */
  public function create()
  {
    return Inertia::render('Pharmacy/Items/Create');
  }

  /**
   * Store a newly created pharmacy item
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'type' => 'required|in:medication,instrument,supply',
      'description' => 'nullable|string',
      'code' => 'required|string|unique:pharmacy_items,code',
      'laboratory' => 'nullable|string|max:255',
      'unit_price' => 'required|numeric|min:0',
      'current_stock' => 'required|integer|min:0',
      'minimum_stock' => 'required|integer|min:0',
      'reorder_point' => 'required|integer|min:0',
      'unit_measurement' => 'required|string|max:255',
      'expiration_date' => 'nullable|date',
      'batch_number' => 'nullable|string|max:255',
      'requires_sterilization' => 'boolean',
      'last_sterilization_date' => 'nullable|date',
      'next_sterilization_date' => 'nullable|date',
      'status' => 'required|in:active,inactive,discontinued',
      'notes' => 'nullable|string',
    ]);

    $item = PharmacyItem::create($validated);

    // Register initial stock movement if stock > 0
    if ($validated['current_stock'] > 0) {
      PharmacyStockMovement::create([
        'pharmacy_item_id' => $item->id,
        'movement_type' => 'entry',
        'quantity' => $validated['current_stock'],
        'stock_before' => 0,
        'stock_after' => $validated['current_stock'],
        'user_id' => Auth::id(),
        'reference' => 'Stock inicial',
        'notes' => 'Creación de item',
      ]);
    }

    return redirect()->route('pharmacy.items.index')
      ->with('success', 'Item creado exitosamente');
  }

  /**
   * Display the specified pharmacy item
   */
  public function show(PharmacyItem $item)
  {
    $item->load(['stockMovements.user', 'requestItems.pharmacyRequest.requestedBy']);

    return Inertia::render('Pharmacy/Items/Show', [
      'item' => $item,
    ]);
  }

  /**
   * Show the form for editing the specified pharmacy item
   */
  public function edit(PharmacyItem $item)
  {
    return Inertia::render('Pharmacy/Items/Create', [
      'item' => $item,
    ]);
  }

  /**
   * Update the specified pharmacy item
   */
  public function update(Request $request, PharmacyItem $item)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'type' => 'required|in:medication,instrument,supply',
      'description' => 'nullable|string',
      'code' => 'required|string|unique:pharmacy_items,code,' . $item->id,
      'laboratory' => 'nullable|string|max:255',
      'unit_price' => 'required|numeric|min:0',
      'current_stock' => 'required|integer|min:0',
      'minimum_stock' => 'required|integer|min:0',
      'reorder_point' => 'required|integer|min:0',
      'unit_measurement' => 'required|string|max:255',
      'expiration_date' => 'nullable|date',
      'batch_number' => 'nullable|string|max:255',
      'requires_sterilization' => 'boolean',
      'last_sterilization_date' => 'nullable|date',
      'next_sterilization_date' => 'nullable|date',
      'status' => 'required|in:active,inactive,discontinued',
      'notes' => 'nullable|string',
    ]);

    // Check if stock changed
    $stockChanged = $validated['current_stock'] != $item->current_stock;
    $oldStock = $item->current_stock;

    $item->update($validated);

    // Register stock movement if changed
    if ($stockChanged) {
      $quantity = $validated['current_stock'] - $oldStock;
      PharmacyStockMovement::create([
        'pharmacy_item_id' => $item->id,
        'movement_type' => 'adjustment',
        'quantity' => $quantity,
        'stock_before' => $oldStock,
        'stock_after' => $validated['current_stock'],
        'user_id' => Auth::id(),
        'reference' => 'Ajuste manual',
        'notes' => 'Actualización de item',
      ]);
    }

    return redirect()->route('pharmacy.items.index')
      ->with('success', 'Item actualizado exitosamente');
  }

  /**
   * Remove the specified pharmacy item (soft delete)
   */
  public function destroy(PharmacyItem $item)
  {
    $item->delete();

    return redirect()->route('pharmacy.items.index')
      ->with('success', 'Item eliminado exitosamente');
  }

  /**
   * Update sterilization information
   */
  public function updateSterilization(Request $request, PharmacyItem $item)
  {
    $validated = $request->validate([
      'last_sterilization_date' => 'required|date',
      'next_sterilization_date' => 'required|date|after:last_sterilization_date',
    ]);

    $item->update($validated);

    return back()->with('success', 'Información de esterilización actualizada');
  }

  /**
   * Adjust stock
   */
  public function adjustStock(Request $request, PharmacyItem $item)
  {
    $validated = $request->validate([
      'adjustment_type' => 'required|in:entry,exit,adjustment,return,expired,damaged',
      'quantity' => 'required|integer|min:1',
      'reference' => 'nullable|string',
      'notes' => 'nullable|string',
    ]);

    DB::transaction(function () use ($item, $validated) {
      $oldStock = $item->current_stock;

      // Calculate new stock based on adjustment type
      if (in_array($validated['adjustment_type'], ['entry', 'return'])) {
        $newStock = $oldStock + $validated['quantity'];
        $quantity = $validated['quantity'];
      } else {
        $newStock = max(0, $oldStock - $validated['quantity']);
        $quantity = -$validated['quantity'];
      }

      // Update stock
      $item->update(['current_stock' => $newStock]);

      // Register movement
      PharmacyStockMovement::create([
        'pharmacy_item_id' => $item->id,
        'movement_type' => $validated['adjustment_type'],
        'quantity' => $quantity,
        'stock_before' => $oldStock,
        'stock_after' => $newStock,
        'user_id' => Auth::id(),
        'reference' => $validated['reference'] ?? null,
        'notes' => $validated['notes'] ?? null,
      ]);
    });

    return back()->with('success', 'Stock ajustado exitosamente');
  }
}
