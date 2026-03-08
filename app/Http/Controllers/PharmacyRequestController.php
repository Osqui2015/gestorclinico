<?php

namespace App\Http\Controllers;

use App\Models\PharmacyRequest;
use App\Models\PharmacyRequestItem;
use App\Models\PharmacyItem;
use App\Models\PharmacyStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PharmacyRequestController extends Controller
{
  /**
   * Display a listing of pharmacy requests
   */
  public function index(Request $request)
  {
    $query = PharmacyRequest::with(['requestedBy', 'patient', 'processedBy', 'items']);

    // Filter by status
    if ($request->has('status') && $request->status !== '') {
      $query->where('status', $request->status);
    } else {
      $query->whereIn('status', ['pending', 'processing']);
    }

    // Filter by priority
    if ($request->has('priority') && $request->priority !== '') {
      $query->where('priority', $request->priority);
    }

    // Search by doctor name or patient name
    if ($request->has('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->whereHas('requestedBy', function ($q) use ($search) {
          $q->where('name', 'like', "%{$search}%");
        })
          ->orWhereHas('patient', function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%");
          });
      });
    }

    $requests = $query->orderBy('priority', 'desc')
      ->orderBy('requested_at', 'asc')
      ->paginate(20);

    return Inertia::render('Pharmacy/Requests/Index', [
      'requests' => $requests,
      'filters' => $request->only(['status', 'priority', 'search']),
    ]);
  }

  /**
   * Show the form for creating a new pharmacy request (for doctors)
   */
  public function create(Request $request)
  {
    $patientId = $request->query('patient_id');
    $appointmentId = $request->query('appointment_id');

    // Get available items
    $items = PharmacyItem::where('status', 'active')
      ->where('current_stock', '>', 0)
      ->orderBy('name')
      ->get();

    return Inertia::render('Pharmacy/Requests/Create', [
      'items' => $items,
      'patientId' => $patientId,
      'appointmentId' => $appointmentId,
    ]);
  }

  /**
   * Store a newly created pharmacy request
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'patient_id' => 'nullable|exists:patients,id',
      'appointment_id' => 'nullable|exists:appointments,id',
      'priority' => 'required|in:low,normal,high,urgent',
      'notes' => 'nullable|string',
      'items' => 'required|array|min:1',
      'items.*.pharmacy_item_id' => 'required|exists:pharmacy_items,id',
      'items.*.quantity_requested' => 'required|integer|min:1',
      'items.*.notes' => 'nullable|string',
    ]);

    DB::transaction(function () use ($validated) {
      // Create the request
      $pharmacyRequest = PharmacyRequest::create([
        'requested_by' => Auth::id(),
        'patient_id' => $validated['patient_id'] ?? null,
        'appointment_id' => $validated['appointment_id'] ?? null,
        'priority' => $validated['priority'],
        'status' => 'pending',
        'notes' => $validated['notes'] ?? null,
        'requested_at' => now(),
      ]);

      // Create request items
      foreach ($validated['items'] as $itemData) {
        PharmacyRequestItem::create([
          'pharmacy_request_id' => $pharmacyRequest->id,
          'pharmacy_item_id' => $itemData['pharmacy_item_id'],
          'quantity_requested' => $itemData['quantity_requested'],
          'notes' => $itemData['notes'] ?? null,
        ]);
      }
    });

    return redirect()->route('pharmacy.requests.index')
      ->with('success', 'Solicitud creada exitosamente');
  }

  /**
   * Display the specified pharmacy request
   */
  public function show(PharmacyRequest $request)
  {
    $request->load([
      'requestedBy',
      'patient',
      'appointment',
      'processedBy',
      'items.pharmacyItem',
      'stockMovements.user'
    ]);

    return Inertia::render('Pharmacy/Requests/Show', [
      'pharmacyRequest' => $request,
    ]);
  }

  /**
   * Process a pharmacy request (start processing)
   */
  public function process(PharmacyRequest $pharmacyRequest)
  {
    if ($pharmacyRequest->status !== 'pending') {
      return back()->with('error', 'Esta solicitud ya está siendo procesada o ha sido completada');
    }

    $pharmacyRequest->update([
      'status' => 'processing',
      'processed_by' => Auth::id(),
      'processed_at' => now(),
    ]);

    return back()->with('success', 'Solicitud en proceso');
  }

  /**
   * Deliver items from a pharmacy request
   */
  public function deliver(Request $request, PharmacyRequest $pharmacyRequest)
  {
    $validated = $request->validate([
      'items' => 'required|array',
      'items.*.id' => 'required|exists:pharmacy_request_items,id',
      'items.*.quantity_delivered' => 'required|integer|min:0',
      'pharmacy_notes' => 'nullable|string',
    ]);

    DB::transaction(function () use ($validated, $pharmacyRequest) {
      foreach ($validated['items'] as $itemData) {
        $requestItem = PharmacyRequestItem::findOrFail($itemData['id']);
        $quantityToDeliver = $itemData['quantity_delivered'];

        if ($quantityToDeliver > 0) {
          // Update delivered quantity
          $newDelivered = $requestItem->quantity_delivered + $quantityToDeliver;
          $requestItem->update([
            'quantity_delivered' => $newDelivered,
          ]);

          // Update stock
          $pharmacyItem = $requestItem->pharmacyItem;
          $oldStock = $pharmacyItem->current_stock;
          $newStock = max(0, $oldStock - $quantityToDeliver);

          $pharmacyItem->update([
            'current_stock' => $newStock,
          ]);

          // Register stock movement
          PharmacyStockMovement::create([
            'pharmacy_item_id' => $pharmacyItem->id,
            'movement_type' => 'exit',
            'quantity' => -$quantityToDeliver,
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'user_id' => Auth::id(),
            'pharmacy_request_id' => $pharmacyRequest->id,
            'reference' => 'Solicitud #' . $pharmacyRequest->id,
            'notes' => 'Entrega a ' . $pharmacyRequest->requestedBy->name,
          ]);
        }
      }

      // Check if all items are delivered
      $allDelivered = $pharmacyRequest->items->every(function ($item) {
        return $item->isFullyDelivered();
      });

      if ($allDelivered) {
        $pharmacyRequest->update([
          'status' => 'completed',
          'completed_at' => now(),
          'pharmacy_notes' => $validated['pharmacy_notes'] ?? null,
        ]);
      } else {
        $pharmacyRequest->update([
          'pharmacy_notes' => $validated['pharmacy_notes'] ?? null,
        ]);
      }
    });

    return back()->with('success', 'Items entregados exitosamente');
  }

  /**
   * Cancel a pharmacy request
   */
  public function cancel(Request $request, PharmacyRequest $pharmacyRequest)
  {
    if ($pharmacyRequest->status === 'completed') {
      return back()->with('error', 'No se puede cancelar una solicitud completada');
    }

    $validated = $request->validate([
      'cancellation_reason' => 'required|string',
    ]);

    $pharmacyRequest->update([
      'status' => 'cancelled',
      'pharmacy_notes' => $validated['cancellation_reason'],
    ]);

    return back()->with('success', 'Solicitud cancelada');
  }

  /**
   * Get requests for a specific doctor (for their own view)
   */
  public function myRequests(Request $request)
  {
    $query = PharmacyRequest::with(['patient', 'processedBy', 'items.pharmacyItem'])
      ->where('requested_by', Auth::id());

    // Filter by status
    if ($request->has('status') && $request->status !== '') {
      $query->where('status', $request->status);
    }

    $requests = $query->orderBy('requested_at', 'desc')
      ->paginate(20);

    return Inertia::render('Doctor/PharmacyRequests', [
      'requests' => $requests,
      'filters' => $request->only(['status']),
    ]);
  }
}
