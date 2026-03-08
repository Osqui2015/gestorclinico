<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSecretaryOrAdmin
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

        if (!in_array($user->role, ['secretary', 'admin'], true)) {
            abort(403, 'Solo secretarios o administradores pueden acceder a esta seccion.');
        }

        return $next($request);
    }
}
