<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateClientOrEditor
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (Auth::guard('client')->check() || Auth::guard('client_user')->check()) {
            $clientId = Auth::guard('client')->id()
                ?? Auth::guard('client_user')->user()?->client_id;

            app()->instance('client_panel_client_id', $clientId);

            return $next($request);
        }

        return redirect()->route('filament.cliente.auth.login');
    }
}
