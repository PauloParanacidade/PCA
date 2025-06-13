<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateController extends Controller
{
    public function impersonate(User $user)
    {
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Apenas administradores podem usar esta funcionalidade.');
        }

        if ($user->hasRole('admin')) {
            return redirect()->back()->with('error', 'Não é possível impersonar outro administrador.');
        }

        // Armazena o ID do usuário original
        Session::put('original_user_id', Auth::id());
        Session::put('impersonate', $user->id);

        // Faz login como o usuário alvo
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Você está agora visualizando como ' . $user->name);
    }
} 