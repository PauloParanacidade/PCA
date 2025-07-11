<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .card-clean {
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }
        
        .dark .card-clean,
        [data-theme="dark"] .card-clean,
        html.dark .card-clean {
            background: #4a5568 !important;
            border: 1px solid #6b7280 !important;
            backdrop-filter: blur(10px);
        }
        
        .clean-bg {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }
        
        .dark .clean-bg,
        [data-theme="dark"] .clean-bg,
        html.dark .clean-bg {
            background: #000000 !important;
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        .animate-slide-up {
            animation: slideUp 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0; 
                transform: translateY(30px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        .input-focus {
            transition: all 0.3s ease;
        }

        .input-focus:focus {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(0);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.4);
        }

        .btn-primary:hover::before {
            opacity: 1;
        }

        .dark .btn-primary:hover {
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.5);
        }

        .logo-float {
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }

        .card-shadow {
            box-shadow: 
                0 20px 25px -5px rgba(0, 0, 0, 0.08), 
                0 10px 10px -5px rgba(0, 0, 0, 0.03),
                0 0 0 1px rgba(0, 0, 0, 0.02);
        }

        .dark .card-shadow {
            box-shadow: 
                0 20px 25px -5px rgba(0, 0, 0, 0.4), 
                0 10px 10px -5px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.05);
        }

        .toggle-btn {
            position: relative;
            overflow: hidden;
        }

        .toggle-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transition: left 0.5s;
        }

        .toggle-btn.active::before {
            left: 100%;
        }

        .input-clean {
            border: 1.5px solid rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.2s ease;
        }

        .dark .input-clean,
        [data-theme="dark"] .input-clean,
        html.dark .input-clean {
            border: 1.5px solid #9ca3af !important;
            background: #9ca3af !important;
        }

        .input-clean:focus {
            border-color: #2563eb;
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .dark .input-clean:focus,
        [data-theme="dark"] .input-clean:focus,
        html.dark .input-clean:focus {
            background: #d1d5db !important;
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
    </style>
</head>

<body class="clean-bg min-h-screen flex items-center justify-center p-4">
    <!-- Elementos decorativos sutis -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 right-10 w-20 h-20 bg-gray-200/30 dark:bg-gray-700/20 rounded-full filter blur-2xl"></div>
        <div class="absolute bottom-1/3 left-10 w-16 h-16 bg-gray-300/20 dark:bg-gray-600/15 rounded-full filter blur-xl"></div>
        <div class="absolute top-3/4 right-1/3 w-12 h-12 bg-gray-400/15 dark:bg-gray-500/10 rounded-full filter blur-lg"></div>
    </div>

    <!-- Botão de teste para modo escuro (temporário) -->
    <div class="fixed top-4 right-4 z-50">
        <button id="dark-mode-toggle" class="p-2 bg-gray-200 dark:bg-gray-700 rounded-lg shadow-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
            <svg class="w-5 h-5 text-gray-800 dark:text-gray-200" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>

    <div class="relative z-10 w-full max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row items-center justify-center gap-12 lg:gap-20">
            
            <!-- Seção Principal - Informações do Sistema -->
            <div class="flex-1 max-w-lg animate-fade-in">
                <div class="text-center lg:text-left mb-8">
                    <!-- Logo animada -->
                    <div class="flex justify-center lg:justify-start mb-10">
                        <img src="{{ asset('images/paranacidade-logo.png') }}" 
                             alt="Logo Paranacidade" 
                             class="h-24 md:h-28 logo-float">
                </div>

                    <!-- Título principal -->
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4 leading-tight">
                        PCA
                        <span class="block text-2xl md:text-3xl font-medium text-gray-800 dark:text-gray-200 mt-2">
                            Paranacidade
                        </span>
                    </h1>
                    
                    <!-- Descrição -->
                    <p class="text-lg md:text-xl text-gray-700 dark:text-gray-300 font-light leading-relaxed">
                        Plano de Contratações Anuais<br>
                    </p>
                    
                    <!-- Elementos visuais -->
                    <div class="hidden lg:flex mt-12 space-x-2 items-end">
                        <div class="w-1 h-8 bg-gray-400 dark:bg-gray-400 rounded-full opacity-80"></div>
                        <div class="w-1 h-6 bg-gray-500 dark:bg-gray-300 rounded-full opacity-60"></div>
                        <div class="w-1 h-4 bg-gray-600 dark:bg-gray-200 rounded-full opacity-40"></div>
                        <div class="w-1 h-2 bg-gray-700 dark:bg-gray-100 rounded-full opacity-20"></div>
                </div>
                </div>
            </div>

            <!-- Seção de Login -->
            <div class="w-full max-w-md animate-slide-up">
        @guest
                    <div class="card-clean rounded-3xl p-10 card-shadow">
                        <div class="text-center mb-10">
                            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-3">
                                Bem-vindo de volta
                            </h2>
                            <p class="text-gray-800 dark:text-gray-200">
                                Acesse sua conta para continuar
                            </p>
                        </div>

                        <!-- Mensagens de erro/sucesso -->
                @if ($errors->any())
                            <div class="mb-6 p-4 rounded-xl bg-red-50/80 dark:bg-red-900 border border-red-200/50 dark:border-red-700 animate-fade-in backdrop-blur-sm">
                                <div class="flex items-center">
                            <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-500 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                        <p class="text-sm font-medium text-red-700 dark:text-red-300">
                                            Credenciais inválidas. Verifique seu email e senha.
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
                            <div class="mb-6 p-4 rounded-xl {{ $isSuccess ? 'bg-green-50/80 border-green-200/50 dark:bg-green-900 dark:border-green-700' : 'bg-red-50/80 border-red-200/50 dark:bg-red-900 dark:border-red-700' }} border animate-fade-in backdrop-blur-sm">
                                <div class="flex items-center">
                            <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 {{ $isSuccess ? 'text-green-500 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                        <p class="text-sm font-medium {{ $isSuccess ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                    {{ __($status) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                        <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                            <input type="hidden" name="user_type" id="form_user_type" value="{{ session('user_type', old('user_type', 'ldap')) }}">

                            <!-- Seletor de tipo de usuário -->
                            <div class="space-y-3">
                                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                    Tipo de acesso
                                </label>
                                <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100/50 dark:bg-gray-700 rounded-xl backdrop-blur-sm">
                            <button type="button" id="user_type_ldap"
                                            class="toggle-btn px-4 py-3 text-sm font-medium rounded-lg transition-all duration-300 ease-in-out bg-blue-600 text-white shadow-sm">
                                        <div class="flex items-center justify-center space-x-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Paranacidade</span>
                                        </div>
                            </button>
                            <button type="button" id="user_type_externo"
                                            class="toggle-btn px-4 py-3 text-sm font-medium rounded-lg transition-all duration-300 ease-in-out text-gray-800 dark:text-gray-200 hover:bg-white/70 dark:hover:bg-gray-600">
                                        <div class="flex items-center justify-center space-x-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Usuário Externo</span>
                                        </div>
                            </button>
                        </div>
                    </div>

                            <!-- Campo de email/usuário -->
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-gray-900 dark:text-white">
                            <span id="email_label">Usuário</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="email" id="email" required
                                           class="input-focus input-clean block w-full px-4 py-3 rounded-xl dark:text-black text-gray-900 placeholder-gray-600 dark:placeholder-gray-700"
                                           value="{{ old('email') }}"
                                           placeholder="Digite seu usuário">
                                    <div id="email_domain" class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-600 dark:text-gray-700 text-sm">
                                @paranacidade.org.br
                            </div>
                        </div>
                    </div>

                            <!-- Campo de senha -->
                            <div class="space-y-2">
                                <label for="password" class="block text-sm font-medium text-gray-900 dark:text-white">
                            Senha
                        </label>
                        <input type="password" name="password" id="password" required
                                       class="input-focus input-clean block w-full px-4 py-3 rounded-xl dark:text-black text-gray-900 placeholder-gray-600 dark:placeholder-gray-700"
                                       placeholder="Digite sua senha">
                    </div>

                            <!-- Opções adicionais -->
                    <div class="flex items-center justify-between">
                                <label for="remember_me" class="flex items-center">
                                    <input id="remember_me" type="checkbox" name="remember"
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-800 dark:text-gray-200">Lembrar-me</span>
                        </label>

                        <div id="forgot_password_link" class="hidden">
                            <a href="{{ route('password.request') }}"
                                       class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors">
                                Esqueceu sua senha?
                            </a>
                        </div>
                    </div>

                            <!-- Botão de login -->
                            <button type="submit" id="submit_btn"
                                    class="btn-primary w-full py-3 px-4 border border-transparent rounded-xl text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                                <span id="submit_text">Entrar</span>
                    </button>
                </form>
            </div>
        @endguest

        @auth
                    <div class="card-clean rounded-3xl p-10 card-shadow text-center">
                        <div class="mb-6">
                            <div class="w-16 h-16 bg-green-100/80 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                Bem-vindo!
                            </h2>
                            <p class="text-gray-800 dark:text-gray-200">
                                Você já está logado no sistema
                            </p>
                        </div>
                        
                <a href="{{ url('/home') }}"
                           class="btn-primary inline-flex items-center justify-center py-3 px-6 border border-transparent rounded-xl text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            Ir para o Dashboard
                </a>
            </div>
        @endauth
    </div>
        </div>
    </div>

<script>
        // Script para forçar o funcionamento do modo escuro
        function updateDarkMode() {
            const isDark = document.documentElement.classList.contains('dark') || 
                          localStorage.getItem('theme') === 'dark' ||
                          (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
                document.body.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.removeAttribute('data-theme');
                document.body.classList.remove('dark');
            }
        }

        // Executa no carregamento
        updateDarkMode();

                 // Observa mudanças no tema
        const observer = new MutationObserver(updateDarkMode);
        observer.observe(document.documentElement, { 
            attributes: true, 
            attributeFilter: ['class'] 
        });

        // Função para toggle do modo escuro
        function toggleDarkMode() {
            const currentTheme = localStorage.getItem('theme');
            const isDarkMode = currentTheme === 'dark' || 
                              (!currentTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);
            
            if (isDarkMode) {
                localStorage.setItem('theme', 'light');
                document.documentElement.classList.remove('dark');
            } else {
                localStorage.setItem('theme', 'dark');
                document.documentElement.classList.add('dark');
            }
            
            updateDarkMode();
        }

    document.addEventListener('DOMContentLoaded', function() {
            // Adiciona evento ao botão de toggle
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', toggleDarkMode);
            }
        const ldapButton = document.getElementById('user_type_ldap');
        const externoButton = document.getElementById('user_type_externo');
        const emailInput = document.getElementById('email');
        const emailLabel = document.getElementById('email_label');
        const emailDomain = document.getElementById('email_domain');
        const passwordInput = document.getElementById('password');
            const submitButton = document.getElementById('submit_btn');
            const submitText = document.getElementById('submit_text');
        const form = document.querySelector('form');
        const formUserType = document.getElementById('form_user_type');
        let currentUserType = formUserType.value;

        function updateFormForUserType(isLdap) {
                // Atualiza os botões com animação
            if (isLdap) {
                    ldapButton.classList.add('bg-blue-600', 'text-white', 'shadow-sm', 'active');
                    ldapButton.classList.remove('text-gray-800', 'dark:text-gray-200', 'hover:bg-white/70', 'dark:hover:bg-gray-600');
                    externoButton.classList.add('text-gray-800', 'dark:text-gray-200', 'hover:bg-white/70', 'dark:hover:bg-gray-600');
                    externoButton.classList.remove('bg-blue-600', 'text-white', 'shadow-sm', 'active');
            } else {
                    externoButton.classList.add('bg-blue-600', 'text-white', 'shadow-sm', 'active');
                    externoButton.classList.remove('text-gray-800', 'dark:text-gray-200', 'hover:bg-white/70', 'dark:hover:bg-gray-600');
                    ldapButton.classList.add('text-gray-800', 'dark:text-gray-200', 'hover:bg-white/70', 'dark:hover:bg-gray-600');
                    ldapButton.classList.remove('bg-blue-600', 'text-white', 'shadow-sm', 'active');
                }

                // Atualiza o label e configurações do campo
            emailLabel.textContent = isLdap ? 'Usuário' : 'Email';
            emailDomain.style.display = isLdap ? 'flex' : 'none';
                document.getElementById('forgot_password_link').style.display = isLdap ? 'none' : 'flex';

                emailInput.style.paddingRight = isLdap ? '180px' : '16px';
                emailInput.type = isLdap ? 'text' : 'email';
                emailInput.placeholder = isLdap ? 'Digite seu usuário' : 'Digite seu email completo';

                // Limpa os campos ao trocar o tipo
            if (currentUserType !== (isLdap ? 'ldap' : 'externo')) {
                emailInput.value = '';
                passwordInput.value = '';
            }

            formUserType.value = isLdap ? 'ldap' : 'externo';
            currentUserType = formUserType.value;

                // Focus suave no input
                setTimeout(() => emailInput.focus(), 300);
            }

            // Configura estado inicial
            updateFormForUserType(formUserType.value === 'ldap');

            // Event listeners para os botões
        ldapButton.addEventListener('click', () => updateFormForUserType(true));
        externoButton.addEventListener('click', () => updateFormForUserType(false));

            // Previne @ no modo LDAP
        emailInput.addEventListener('input', function(e) {
            if (currentUserType === 'ldap' && e.target.value.includes('@')) {
                e.target.value = e.target.value.replace('@', '');
            }
        });

            // Animação no submit
        form.addEventListener('submit', function(e) {
            formUserType.value = currentUserType;

            submitButton.disabled = true;
            submitButton.classList.add('opacity-75');
                submitText.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Autenticando...
            `;
        });

            // Efeito de hover nos inputs
            const inputs = document.querySelectorAll('.input-focus');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('scale-105');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('scale-105');
                });
            });
    });
</script>
</body>
</html>
