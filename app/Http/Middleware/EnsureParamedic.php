<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureParamedic
{
  /**
   * Handle an incoming request.
   */
  public function handle(Request $request, Closure $next): Response
  {
    $user = Auth::user();

    if (!$user || !in_array($user->role, ['paramedic', 'admin', 'emergency'])) {
      abort(403, 'No tienes acceso al modulo de paramedicos y ambulancias.');
    }

    return $next($request);
  }
}
