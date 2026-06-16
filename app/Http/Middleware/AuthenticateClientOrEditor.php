<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateClientOrEditor
{
    public function handle(Request $request, Closure $next): mixed
    {
        // Si ya está autenticado en alguno de los dos guards, continuar
        if (Auth::guard('client')->check() || Auth::guard('client_user')->check()) {
            return $next($request);
        }

        return redirect()->route('filament.cliente.auth.login');
    }
}
