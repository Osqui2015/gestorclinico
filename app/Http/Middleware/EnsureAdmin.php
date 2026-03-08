<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
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

    if (! $user || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
      abort(403, 'Acceso reservado a administradores.');
    }

    return $next($request);
  }
}
