<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHospitalizationAccess
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $user = $request->user();

    // Solo admin, enfermeros y médicos
    if (!$user || (!$user->isAdmin() && !$user->isNurse() && !$user->isDoctor())) {
      abort(403, 'No tiene acceso al módulo de internación.');
    }

    return $next($request);
  }
}
