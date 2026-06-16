<?php

namespace App\Filament\Cliente\Pages;

use App\Models\ClientUser;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $email = $data['email'];
        $password = $data['password'];
        $remember = $data['remember'] ?? false;

        // Intentar login como Client primero
        if (Auth::guard('client')->attempt(['email' => $email, 'password' => $password], $remember)) {
            $client = Auth::guard('client')->user();
            if (! $client->active) {
                Auth::guard('client')->logout();
                throw ValidationException::withMessages(['email' => 'Tu cuenta está desactivada.']);
            }
            session()->regenerate();
            return app(LoginResponse::class);
        }

        // Intentar login como ClientUser (editor)
        if (Auth::guard('client_user')->attempt(['email' => $email, 'password' => $password], $remember)) {
            $user = Auth::guard('client_user')->user();
            if (! $user->active) {
                Auth::guard('client_user')->logout();
                throw ValidationException::withMessages(['email' => 'Tu cuenta está desactivada.']);
            }
            session()->regenerate();
            return app(LoginResponse::class);
        }

        throw ValidationException::withMessages(['email' => __('filament-panels::pages/auth/login.messages.failed')]);
    }
}
