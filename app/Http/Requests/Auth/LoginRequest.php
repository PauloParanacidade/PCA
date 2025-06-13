<?php
namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'     => ['required', 'string'],
            'password'  => ['required', 'string'],
            'user_type' => ['required', 'string', 'in:ldap,externo'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required'     => 'O campo usuário/email é obrigatório.',
            'password.required'  => 'O campo senha é obrigatório.',
            'user_type.required' => 'O tipo de usuário é obrigatório.',
            'user_type.in'       => 'O tipo de usuário selecionado é inválido.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        try {
            // Verificar qual tipo de autenticação foi selecionado
            $userType    = $this->input('user_type', 'ldap');
            $formatEmail = $this->input('formatEmail', '1') === '1';

            // Preparar email com base no tipo de usuário
            if ($userType === 'ldap') {
                $username = str_contains($this->email, '@')
                ? explode('@', $this->email)[0]
                : $this->email;

                // Para usuários LDAP, completa com o domínio se não tiver @
                $email = str_contains($this->email, '@')
                ? $this->email
                : $this->email . '@paranacidade.org.br';
            } else {
                // Para usuários externos, usa o email como fornecido
                $email    = $this->email;
                $username = $this->email;
            }

            Log::info('Tentativa de login', [
                'email'     => $email,
                'user_type' => $userType,
                'username'  => $username,
            ]);

            // Se for usuário LDAP, tenta autenticar primeiro
            if ($userType === 'ldap') {
                // Tenta autenticar com o email no LDAP
                $credentials = [
                    'email'    => $email,
                    'password' => $this->password,
                ];

                // Também tenta pelo username
                $credentialsWithUsername = [
                    'samaccountname' => $username,
                    'password'       => $this->password,
                ];

                // Tenta primeiro com email
                $attemptResult = Auth::guard('ldap')->attempt($credentials, $this->boolean('remember'));
                Log::info('Tentativa de autenticação LDAP com email', [
                    'email'  => $email,
                    'result' => $attemptResult,
                ]);

                // Se não funcionar, tenta com username
                if (! $attemptResult) {
                    $attemptResult = Auth::guard('ldap')->attempt($credentialsWithUsername, $this->boolean('remember'));
                    Log::info('Tentativa de autenticação LDAP com username', [
                        'username' => $username,
                        'result'   => $attemptResult,
                    ]);
                }

                if ($attemptResult) {
                    // Se a autenticação LDAP foi bem-sucedida, busca ou cria o usuário
                    $ldapUser = Auth::guard('ldap')->user();

                    if ($ldapUser) {
                        Log::info('Usuário LDAP autenticado', [
                            'email'           => $email,
                            'ldap_attributes' => $ldapUser->attributes,
                        ]);

                        // Busca o usuário LDAP no banco local
                        $user = User::where('email', $email)
                            ->where('isExterno', false)
                            ->first();

                        if (! $user) {
                            // Tenta buscar por username
                            $user = User::where('username', $username)
                                ->where('isExterno', false)
                                ->first();
                        }

                        if (! $user) {
                            // Se não existe, cria o usuário
                            $user            = new User();
                            $user->username  = $username;
                            $user->email     = $email;
                            $user->name      = $ldapUser->displayname[0] ?? $username;
                            $user->guid      = $ldapUser->objectguid[0] ?? null;
                            $user->domain    = 'default';
                            $user->isExterno = false;
                            $user->active    = true;
                            $user->save();

                            Log::info('Novo usuário LDAP criado', [
                                'user_id' => $user->id,
                                'email'   => $email,
                            ]);
                        } else {
                            // Atualiza o usuário existente
                            $user->update([
                                'email'     => $email,
                                'username'  => $username,
                                'active'    => true,
                                'isExterno' => false,
                            ]);

                            Log::info('Usuário LDAP atualizado', [
                                'user_id' => $user->id,
                                'email'   => $email,
                            ]);
                        }

                        // Faz login com o usuário local
                        Auth::login($user, $this->boolean('remember'));
                        RateLimiter::clear($this->throttleKey());
                        return;
                    }
                }

                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => 'Credenciais LDAP inválidas. Verifique se seu email e senha estão corretos e se você está no grupo correto do Active Directory. Em caso de dúvidas entre em contato com o suporte de TI.',
                ]);
            }

            // Se for usuário externo
            if ($userType === 'externo') {
                // Busca o usuário externo no banco
                $user = User::where('email', $email)
                    ->where('isExterno', true)
                    ->first();

                // Se não encontrou um usuário externo com esse email, tenta com username
                if (! $user) {
                    $user = User::where('username', $email)
                        ->where('isExterno', true)
                        ->first();
                }

                // Tentativa de autenticação para usuário externo
                $credentials = [];

                if ($user) {
                    // Se encontrou o usuário, usa o email dele
                    $credentials = [
                        'email'    => $user->email,
                        'password' => $this->password,
                    ];
                } else {
                    // Se não encontrou, tenta usar o email fornecido
                    $credentials = [
                        'email'    => $email,
                        'password' => $this->password,
                    ];
                }

                Log::info('Tentando autenticação externa', [
                    'email'       => $email,
                    'user_email'  => $user->email ?? null,
                    'user_exists' => (bool) $user,
                ]);

                if (Auth::attempt($credentials, $this->boolean('remember'))) {
                    RateLimiter::clear($this->throttleKey());
                    return;
                }

                // Se falhou a autenticação externa, mostra mensagem específica
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => 'Credenciais inválidas. Verifique seu email e senha.',
                ]);
            }
        } catch (ValidationException $e) {
            Log::error('Erro na autenticação', [
                'messages' => $e->getMessage(),
                'email'    => $email ?? null,
            ]);
            throw $e;
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
    }
}
