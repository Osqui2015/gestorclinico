<?php

namespace App\Http\Controllers;

use App\Models\PharmacyItem;
use App\Models\PharmacyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PharmacyController extends Controller
{
  /**
   * Display the pharmacy dashboard
   */
  public function index()
  {
    $user = Auth::user();

    if ($user->role !== 'pharmacy' && $user->role !== 'admin') {
      abort(403, 'No tienes acceso a esta sección');
    }

    // Get alerts
    $lowStockItems = PharmacyItem::lowStock()
      ->where('status', 'active')
      ->orderBy('current_stock', 'asc')
      ->limit(10)
      ->get();

    $expiringSoonItems = PharmacyItem::expiringSoon()
      ->where('status', 'active')
      ->orderBy('expiration_date', 'asc')
      ->limit(10)
      ->get();

    $expiredItems = PharmacyItem::expired()
      ->where('status', 'active')
      ->orderBy('expiration_date', 'asc')
      ->limit(10)
      ->get();

    $sterilizationDueItems = PharmacyItem::sterilizationDue()
      ->where('status', 'active')
      ->orderBy('next_sterilization_date', 'asc')
      ->limit(10)
      ->get();

    // Get pending requests
    $pendingRequests = PharmacyRequest::with(['requestedBy', 'patient', 'items.pharmacyItem'])
      ->pending()
      ->orderBy('priority', 'desc')
      ->orderBy('requested_at', 'asc')
      ->limit(10)
      ->get();

    // Get statistics
    $totalItems = PharmacyItem::where('status', 'active')->count();
    $totalMedications = PharmacyItem::where('type', 'medication')->where('status', 'active')->count();
    $totalInstruments = PharmacyItem::where('type', 'instrument')->where('status', 'active')->count();
    $totalSupplies = PharmacyItem::where('type', 'supply')->where('status', 'active')->count();
    $totalPendingRequests = PharmacyRequest::pending()->count();
    $totalProcessingRequests = PharmacyRequest::processing()->count();

    return Inertia::render('Pharmacy/Dashboard', [
      'alerts' => [
        'lowStock' => $lowStockItems,
        'expiringSoon' => $expiringSoonItems,
        'expired' => $expiredItems,
        'sterilizationDue' => $sterilizationDueItems,
      ],
      'pendingRequests' => $pendingRequests,
      'statistics' => [
        'totalItems' => $totalItems,
        'totalMedications' => $totalMedications,
        'totalInstruments' => $totalInstruments,
        'totalSupplies' => $totalSupplies,
        'totalPendingRequests' => $totalPendingRequests,
        'totalProcessingRequests' => $totalProcessingRequests,
      ],
    ]);
  }
}
