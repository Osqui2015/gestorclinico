<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePreAdmissionAccess
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $user = $request->user();

    if (!$user || (!$user->isSecretary() && !$user->isAdmin())) {
      abort(403, 'Usuario no autorizado para acceder a pre-internaciones');
    }

    return $next($request);
  }
}
