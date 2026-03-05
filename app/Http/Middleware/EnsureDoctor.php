<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureDoctor
{
  /**
   * Handle an incoming request.
   */
  public function handle(Request $request, Closure $next)
  {
    $user = auth()->user();

    if (!$user) {
      abort(403, 'No autenticado.');
    }

    if ($user->role !== 'doctor') {
      abort(403, 'Solo los doctores pueden acceder a esta sección.');
    }

    return $next($request);
  }
}
