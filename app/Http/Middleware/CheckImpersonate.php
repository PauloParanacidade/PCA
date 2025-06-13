<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckImpersonate
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('impersonate')) {
            $user = User::find(session('impersonate'));
            if ($user) {
                Auth::login($user);
            }
        }

        return $next($request);
    }
} 