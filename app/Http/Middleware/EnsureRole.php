<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureRole
{
  /**
   * Handle an incoming request.
   */
  public function handle(Request $request, Closure $next)
  {
    $user = Auth::user();

    if ($user && !($user instanceof User)) {
      $user = null;
    }

    if (! $user) {
      abort(403, 'No autenticado.');
    }

    // Allow only doctors and admins for sensitive routes by default
    if (method_exists($user, 'isDoctor') && $user->isDoctor()) {
      return $next($request);
    }

    if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
      return $next($request);
    }

    abort(403, 'No tienes permiso para acceder a esta sección.');
  }
}
