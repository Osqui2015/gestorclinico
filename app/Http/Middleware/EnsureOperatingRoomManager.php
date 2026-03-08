<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOperatingRoomManager
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

    if (!in_array($user->role, ['admin', 'operating_room_manager'], true)) {
      abort(403, 'Solo administración o encargado de quirófano puede gestionar esta sección.');
    }

    return $next($request);
  }
}
