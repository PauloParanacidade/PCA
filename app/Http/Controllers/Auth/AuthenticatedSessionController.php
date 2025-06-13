<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('welcome');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Determina o tipo de usuário e formata o email/username adequadamente
        $userType = $request->input('user_type', 'ldap');
        $email    = $request->email;

        // Salva o tipo de usuário na sessão
        session(['user_type' => $userType]);

        if ($userType === 'ldap' && ! str_contains($email, '@')) {
            $email = $email . '@paranacidade.org.br';
        }

        // Verificar se o usuário existe e está ativo
        // Refinar a busca de usuário com base no tipo selecionado
        if ($userType === 'ldap') {
            $user = User::where(function ($query) use ($email, $request) {
                $query->where('email', 'LIKE', '%@paranacidade.org.br')
                    ->where(function ($q) use ($email, $request) {
                        $q->where('email', $email)
                            ->orWhere('username', $request->email);
                    });
            })->where('isExterno', false)->first();
        } else {
            // Para usuário externo
            $user = User::where('email', $email)
                ->where('isExterno', true)
                ->first();
        }

        if (! $user) {
            // Se não encontrou usuário com o tipo específico, busca qualquer usuário com o email/username
            $user = User::where('email', $email)
                ->orWhere('email', $request->email)
                ->orWhere('username', $request->email)
                ->first();
        }

        if ($user && ! $user->active) {
            return back()->with('status', 'Sua conta está desativada. Por favor, entre em contato com o suporte de TI.');
        }

        try {
            // Passa o usuário encontrado para o método authenticate
            $request->authenticate();
            $request->session()->regenerate();

            // Registrar o tipo de login bem-sucedido para fins de auditoria
            \Illuminate\Support\Facades\Log::info('Login bem-sucedido', [
                'user_id' => Auth::id(),
                'email'   => $email,
                'type'    => $userType,
                'ip'      => $request->ip(),
                'user'    => Auth::user() ? [
                    'id'        => Auth::user()->id,
                    'email'     => Auth::user()->email,
                    'username'  => Auth::user()->username,
                    'isExterno' => Auth::user()->isExterno,
                ] : null,
            ]);

            return redirect()->intended(RouteServiceProvider::HOME);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Erro na autenticação', [
                'message' => $e->getMessage(),
                'email'   => $email,
                'type'    => $userType,
            ]);

            return back()->withErrors([
                'email' => $e->getMessage(),
            ])->withInput(['email' => $request->email]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro inesperado na autenticação', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()->with('status', 'Ocorreu um erro durante a autenticação. Por favor, tente novamente ou entre em contato com o suporte.')->withInput(['email' => $request->email]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
