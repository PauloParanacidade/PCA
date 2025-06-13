<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
        }
    </style>
</head>

<body
    class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-4 md:p-6 lg:p-8 items-center justify-center min-h-screen">
    <div class="flex flex-col items-center justify-center w-full gap-4 md:gap-6 lg:gap-8 max-w-7xl mx-auto">
        <!-- Bloco Principal -->
        <main
            class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg w-full overflow-hidden">
            <div class="flex flex-col lg:flex-row">
                <!-- Logo em telas pequenas -->
                <div
                    class="lg:hidden w-full bg-white dark:bg-[#161615] p-6 flex justify-center border-b border-[#19140035] dark:border-[#3E3E3A]">
                    <img src="{{ asset('images/paranacidade-logo.png') }}" alt="Logo Paranacidade" class="h-16 md:h-20">
                </div>

                <!-- Seção de Informações -->
                <div class="flex-1 p-6 md:p-8 lg:p-20">
                    <h1 class="text-xl md:text-2xl lg:text-3xl font-semibold mb-3 md:mb-4 dark:text-[#EDEDEC]">
                        Sistema de Login do Paranacidade</h1>
                    <h2 class="text-lg md:text-xl font-medium mb-2 dark:text-[#EDEDEC]">Paranacidade</h2>

                    <p class="text-sm md:text-base text-[#706f6c] dark:text-[#A1A09A] font-normal">
                        Sistema de login simples do Paranacidade.
                    </p>
                </div>

                <!-- Logo em telas grandes -->
                <div
                    class="hidden lg:flex lg:w-[438px] shrink-0 items-center justify-center p-8 lg:border-l border-[#19140035] dark:border-[#3E3E3A]">
                    <img src="{{ asset('images/paranacidade-logo.png') }}" alt="Logo Paranacidade"
                        class="w-full max-w-[300px]">
                </div>
            </div>
        </main>

        <!-- Bloco de Login -->
        @guest
            <div
                class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg w-full lg:max-w-[400px] p-6 md:p-8">
                <h3 class="text-lg md:text-xl font-semibold mb-4 dark:text-[#EDEDEC] text-center">Acesso ao Sistema</h3>

                @if ($errors->any())
                    <div class="mb-4 p-4 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400 dark:text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                    Credenciais inválidas. Por favor, verifique seu email e senha.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('status'))
                    @php
                        $status = session('status');
                        $isSuccess = in_array($status, [__('passwords.reset'), 'Sua senha foi redefinida!']);
                    @endphp
                    <div
                        class="mb-4 p-4 rounded-md {{ $isSuccess ? 'bg-green-50 border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-200' : 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-200' }} border">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 {{ $isSuccess ? 'text-green-400 dark:text-green-500' : 'text-red-400 dark:text-red-500' }}"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">
                                    {{ __($status) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="user_type" id="form_user_type"
                        value="{{ session('user_type', old('user_type', 'ldap')) }}">

                    <!-- Tipo de Usuário -->
                    <div class="flex items-center justify-center mb-4">
                        <div class="inline-flex rounded-md shadow-sm" role="group">
                            <button type="button" id="user_type_ldap"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-gray-200 rounded-l-lg hover:bg-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-white dark:bg-blue-600 dark:border-gray-600 dark:text-white dark:hover:bg-blue-700 dark:focus:ring-blue-500 dark:focus:text-white transition-colors duration-200">
                                Paranacidade
                            </button>
                            <button type="button" id="user_type_externo"
                                class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-lg hover:bg-gray-50 hover:text-gray-900 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600 dark:hover:text-white dark:focus:ring-blue-500 dark:focus:text-white transition-colors duration-200">
                                Usuário Externo
                            </button>
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-[#A1A09A] mb-1">
                            <span id="email_label">Usuário</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="email" id="email" required
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-[#1D1D1B] dark:border-[#3E3E3A] dark:text-[#EDEDEC] text-sm md:text-base font-normal @error('email') border-red-300 dark:border-red-700 @enderror"
                                value="{{ old('email') }}">
                            <div id="email_domain"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-500 dark:text-gray-400 text-sm md:text-base">
                                @paranacidade.org.br
                            </div>
                        </div>
                    </div>

                    <!-- Senha -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-[#A1A09A] mb-1">
                            Senha
                        </label>
                        <input type="password" name="password" id="password" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-[#1D1D1B] dark:border-[#3E3E3A] dark:text-[#EDEDEC] text-sm md:text-base font-normal @error('password') border-red-300 dark:border-red-700 @enderror">
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-[#1D1D1B] dark:border-[#3E3E3A]"
                                name="remember">
                            <span class="ms-2 text-sm text-gray-600 dark:text-[#A1A09A]">Lembrar-me</span>
                        </label>

                        <!-- Link de recuperação de senha (apenas para usuários externos) -->
                        <div id="forgot_password_link" class="hidden">
                            <a href="{{ route('password.request') }}"
                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                Esqueceu sua senha?
                            </a>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full flex justify-center py-2.5 md:py-3 px-4 border border-transparent rounded-md shadow-sm text-sm md:text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-[#161615]">
                        Entrar
                    </button>
                </form>
            </div>
        @endguest

        @auth
            <div
                class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg w-full lg:max-w-[400px] p-6 md:p-8">
                <a href="{{ url('/dashboard') }}"
                    class="w-full flex justify-center py-2.5 md:py-3 px-4 border border-transparent rounded-md shadow-sm text-sm md:text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-[#161615]">
                    Ir para o Dashboard
                </a>
            </div>
        @endauth
    </div>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ldapButton = document.getElementById('user_type_ldap');
        const externoButton = document.getElementById('user_type_externo');
        const emailInput = document.getElementById('email');
        const emailLabel = document.getElementById('email_label');
        const emailDomain = document.getElementById('email_domain');
        const passwordInput = document.getElementById('password');
        const submitButton = document.querySelector('button[type="submit"]');
        const form = document.querySelector('form');
        const formUserType = document.getElementById('form_user_type');
        let currentUserType = formUserType.value;

        function updateFormForUserType(isLdap) {
            // Atualiza os botões
            if (isLdap) {
                ldapButton.classList.add(
                    'bg-blue-600', 'text-white', 'hover:bg-blue-700',
                    'dark:bg-blue-600', 'dark:text-white', 'dark:hover:bg-blue-700'
                );
                ldapButton.classList.remove(
                    'bg-white', 'text-gray-900', 'hover:bg-gray-50', 'hover:text-gray-900',
                    'dark:bg-gray-700', 'dark:text-white', 'dark:hover:bg-gray-600',
                    'dark:hover:text-white', 'dark:focus:ring-blue-500', 'dark:focus:text-white'
                );
                externoButton.classList.add(
                    'bg-white', 'text-gray-900', 'hover:bg-gray-50', 'hover:text-gray-900',
                    'dark:bg-gray-700', 'dark:text-white', 'dark:hover:bg-gray-600',
                    'dark:hover:text-white', 'dark:focus:ring-blue-500', 'dark:focus:text-white'
                );
                externoButton.classList.remove(
                    'bg-blue-600', 'text-white', 'hover:bg-blue-700',
                    'dark:bg-blue-600', 'dark:text-white', 'dark:hover:bg-blue-700'
                );
            } else {
                externoButton.classList.add(
                    'bg-blue-600', 'text-white', 'hover:bg-blue-700',
                    'dark:bg-blue-600', 'dark:text-white', 'dark:hover:bg-blue-700'
                );
                externoButton.classList.remove(
                    'bg-white', 'text-gray-900', 'hover:bg-gray-50', 'hover:text-gray-900',
                    'dark:bg-gray-700', 'dark:text-white', 'dark:hover:bg-gray-600',
                    'dark:hover:text-white', 'dark:focus:ring-blue-500', 'dark:focus:text-white'
                );
                ldapButton.classList.add(
                    'bg-white', 'text-gray-900', 'hover:bg-gray-50', 'hover:text-gray-900',
                    'dark:bg-gray-700', 'dark:text-white', 'dark:hover:bg-gray-600',
                    'dark:hover:text-white', 'dark:focus:ring-blue-500', 'dark:focus:text-white'
                );
                ldapButton.classList.remove(
                    'bg-blue-600', 'text-white', 'hover:bg-blue-700',
                    'dark:bg-blue-600', 'dark:text-white', 'dark:hover:bg-blue-700'
                );
            }

            // Atualiza o label e placeholder do campo de email
            emailLabel.textContent = isLdap ? 'Usuário' : 'Email';

            // Mostra/esconde o sufixo de email para usuários LDAP
            emailDomain.style.display = isLdap ? 'flex' : 'none';

            // Mostra/esconde o link de recuperação de senha
            document.getElementById('forgot_password_link').style.display = isLdap ? 'none' : 'block';

            // Adiciona margem à direita se estiver no modo LDAP para não sobrepor o texto
            emailInput.style.paddingRight = isLdap ? '180px' : '12px';

            // Limpa os campos ao trocar o tipo de usuário
            if (currentUserType !== (isLdap ? 'ldap' : 'externo')) {
                emailInput.value = '';
                passwordInput.value = '';
            }

            // Define o tipo de input
            emailInput.type = isLdap ? 'text' : 'email';

            // Atualiza o placeholder
            emailInput.placeholder = isLdap ? 'Digite seu usuário' : 'Digite seu email completo';

            // Atualiza o campo oculto de tipo de usuário
            formUserType.value = isLdap ? 'ldap' : 'externo';
            currentUserType = formUserType.value;

            // Focus no input após trocar
            emailInput.focus();

            // Atualiza texto do botão submit
            submitButton.textContent = 'Entrar' + (isLdap ? '' : ' (Usuário Externo)');
        }

        // Configura o formulário inicialmente com base no último tipo de usuário
        const initialUserType = formUserType.value;
        updateFormForUserType(initialUserType === 'ldap');

        // Adiciona os event listeners para os botões
        ldapButton.addEventListener('click', () => updateFormForUserType(true));
        externoButton.addEventListener('click', () => updateFormForUserType(false));

        // Impede que o usuário digite @ no input quando estiver no modo LDAP
        emailInput.addEventListener('input', function(e) {
            if (currentUserType === 'ldap' && e.target.value.includes('@')) {
                e.target.value = e.target.value.replace('@', '');
            }
        });

        // Adiciona animação no submit
        form.addEventListener('submit', function(e) {
            // Garante que o tipo de usuário seja enviado
            formUserType.value = currentUserType;

            submitButton.disabled = true;
            submitButton.classList.add('opacity-75');
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Autenticando...
            `;
        });
    });
</script>

</html>
