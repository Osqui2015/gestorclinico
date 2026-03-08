<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePharmacy
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    if (!Auth::check()) {
      return redirect('/login');
    }

    $user = Auth::user();

    if ($user->role !== 'pharmacy' && $user->role !== 'admin') {
      abort(403, 'No tienes acceso a esta sección de farmacia');
    }

    return $next($request);
  }
}
