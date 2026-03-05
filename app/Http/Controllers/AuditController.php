<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditController extends Controller
{
  /**
   * Display a paginated list of audits.
   */
  public function index(Request $request)
  {
    $perPage = (int) $request->get('perPage', 15);

    $query = Audit::query();

    // Filters: type, user, event, date range
    if ($type = $request->get('type')) {
      $query->where('auditable_type', $type);
    }

    if ($userId = $request->get('user_id')) {
      $query->where('user_id', $userId);
    }

    if ($event = $request->get('event')) {
      $query->where('event', $event);
    }

    if ($from = $request->get('from')) {
      $query->whereDate('created_at', '>=', $from);
    }

    if ($to = $request->get('to')) {
      $query->whereDate('created_at', '<=', $to);
    }

    $audits = $query->orderByDesc('created_at')
      ->paginate($perPage)
      ->withQueryString()
      ->through(fn($a) => [
        'id' => $a->id,
        'auditable_type' => $a->auditable_type,
        'auditable_id' => $a->auditable_id,
        'event' => $a->event,
        'user_id' => $a->user_id,
        'old_values' => $a->old_values,
        'new_values' => $a->new_values,
        'created_at' => $a->created_at->toDateTimeString(),
      ]);

    // For filter selects, provide available types and users
    $types = Audit::select('auditable_type')->distinct()->pluck('auditable_type');
    $userIds = Audit::select('user_id')->distinct()->whereNotNull('user_id')->pluck('user_id');
    $users = \App\Models\User::whereIn('id', $userIds)->get(['id', 'name']);

    return Inertia::render('Admin/Audits/Index', [
      'audits' => $audits,
      'types' => $types,
      'users' => $users,
      'filters' => $request->only(['type', 'user_id', 'event', 'from', 'to', 'perPage']),
    ]);
  }

  /**
   * Show a single audit entry.
   */
  public function show(Audit $audit)
  {
    return Inertia::render('Admin/Audits/Show', [
      'audit' => $audit,
    ]);
  }
}
