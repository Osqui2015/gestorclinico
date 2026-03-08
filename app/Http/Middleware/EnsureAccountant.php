<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountant
{
  /**
   * Handle an incoming request.
   */
  public function handle(Request $request, Closure $next): Response
  {
    $user = Auth::user();

    if (!$user || !in_array($user->role, ['accountant', 'admin', 'secretary'])) {
      abort(403, 'No tienes acceso al módulo de cuentas corrientes.');
    }

    return $next($request);
  }
}
