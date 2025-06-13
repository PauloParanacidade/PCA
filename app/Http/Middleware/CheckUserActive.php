<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckUserActive
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('CheckUserActive middleware executado', [
            'user_id' => Auth::id(),
            'is_logged_in' => Auth::check(),
            'is_active' => Auth::check() ? Auth::user()->active : null
        ]);

        if (Auth::check() && !Auth::user()->active) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            
            return redirect()->route('login')->with('status', 'Sua conta está desativada. Por favor, entre em contato com o suporte de TI.');
        }

        return $next($request);
    }
} 