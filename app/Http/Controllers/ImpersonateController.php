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

    public function stopImpersonate()
    {
        // Verifica se existe uma sessão de impersonação ativa
        if (!Session::has('original_user_id') || !Session::has('impersonate')) {
            return redirect()->route('dashboard')->with('error', 'Não há sessão de impersonação ativa.');
        }

        // Recupera o ID do usuário original
        $originalUserId = Session::get('original_user_id');
        $originalUser = User::find($originalUserId);

        if (!$originalUser) {
            // Remove as sessões e redireciona para login se o usuário original não existir
            Session::forget(['original_user_id', 'impersonate']);
            Auth::logout();
            return redirect()->route('login')->with('error', 'Usuário original não encontrado. Faça login novamente.');
        }

        // Verifica se o usuário original ainda tem permissão de admin
        if (!$originalUser->hasRole('admin')) {
            Session::forget(['original_user_id', 'impersonate']);
            Auth::logout();
            return redirect()->route('login')->with('error', 'Usuário original não possui mais permissões de administrador.');
        }

        // Remove as sessões de impersonação
        Session::forget(['original_user_id', 'impersonate']);

        // Faz login como o usuário original
        Auth::login($originalUser);

        return redirect()->route('dashboard')->with('success', 'Você retornou ao seu usuário original.');
    }
} 