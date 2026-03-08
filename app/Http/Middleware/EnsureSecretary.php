<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSecretary
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

    if (!$user) {
      abort(403, 'No autenticado.');
    }

    if ($user->role !== 'secretary') {
      abort(403, 'Solo los secretarios pueden acceder a esta sección.');
    }

    return $next($request);
  }
}
