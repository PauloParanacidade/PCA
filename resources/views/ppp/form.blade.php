@extends('layouts.adminlte-custom')

@php
    use Carbon\Carbon;
@endphp

@php
    $isCreating = !isset($ppp) || !$ppp->id;
    $pageTitle = $isCreating ? 'Criar PPP' : 'Editar PPP';
@endphp

@section('title', $pageTitle)

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-file-contract text-primary mr-2"></i>
                        {{ $pageTitle }}
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('ppp.index') }}">PPPs</a></li>
                        <li class="breadcrumb-item active">{{ $isCreating ? 'Criar' : 'Editar' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Alertas -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h6><i class="fas fa-exclamation-triangle mr-2"></i>Há erros no formulário:</h6>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form method="POST" action="{{ $isCreating ? route('ppp.store') : route('ppp.update', $ppp->id) }}" id="ppp-form">
            @csrf
            @if(!$isCreating)
                @method('PUT')
            @endif

{{-- Primeira linha: Card Azul e Card Amarelo --}}
<div class="row mb-4 align-items-stretch">
    {{-- Card Azul --}}
    <div class="col-lg-6 d-flex">
        @include('ppp.partials.informacoes-item')
    </div>

    {{-- Card Amarelo --}}
    <div class="col-lg-6 d-flex" id="card-amarelo">
        <div class="card-bloqueado {{ $isCreating ? 'bloqueado' : '' }}">
            @include('ppp.partials.contrato-vigente')
        </div>
    </div>
</div> {{-- ← FECHAMENTO DA PRIMEIRA ROW --}}

{{-- Segunda linha: Card Verde e Card Ciano --}}
<div class="row mb-4 align-items-stretch">
    <div class="col-lg-6 d-flex">
        <div class="card-bloqueado {{ $isCreating ? 'bloqueado' : '' }}">
            @include('ppp.partials.informacoes-financeiras')
        </div>
    </div>

    <div class="col-lg-6 d-flex">
        <div class="card-bloqueado {{ $isCreating ? 'bloqueado' : '' }}">
            @include('ppp.partials.vinculacao-dependencia')
        </div>
    </div>
</div>



            {{-- Botões de Ação --}}
            @include('ppp.partials.botoes-acao')
        </form>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/ppp-form.css') }}">
    <style>
        /* Animações para os cards */
        .fade-in-cards {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Estilo para campos inválidos */
        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        /* Estilo para o botão próximo */
        #btn-proximo-card-azul {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            transition: all 0.3s ease;
        }

        #btn-proximo-card-azul:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .col-lg-6 {
                margin-bottom: 1rem;
            }

            #btn-proximo-card-azul {
                width: 100%;
                margin-top: 1rem;
            }
        }

        /* Garantir altura igual dos cards */
        .card {
            height: 100%;
        }

        /* Espaçamento entre as linhas de cards */
        .row.mb-4 {
            margin-bottom: 1.5rem !important;
        }
    </style>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===================================
            // CONFIGURAÇÕES INICIAIS
            // ===================================
            
            const isCreating = {{ $isCreating ? 'true' : 'false' }};
            const btnProximo = document.getElementById('btn-proximo-card-azul');
            const btnSalvarEnviar = document.getElementById('btn-salvar-enviar');
            const btnCancelar = document.getElementById('btn-cancelar');
            
            // ===================================
            // FUNÇÃO PARA DESBLOQUEAR CARDS
            // ===================================
            
            function desbloquearCards() {
                const cardsParaDesbloquear = document.querySelectorAll('.card-bloqueado.bloqueado');
                
                cardsParaDesbloquear.forEach((card, index) => {
                    setTimeout(() => {
                        // Adicionar classe de desbloqueio
                        card.classList.add('desbloqueando');
                        
                        // Remover classe bloqueado após a animação
                        setTimeout(() => {
                            card.classList.remove('bloqueado', 'desbloqueando');
                            card.classList.add('card-desbloqueado');
                            
                            // Remover classe de destaque após a animação
                            setTimeout(() => {
                                card.classList.remove('card-desbloqueado');
                            }, 800);
                        }, 600);
                    }, index * 200); // Delay escalonado para cada card
                });
            }
            
            // ===================================
            // VALIDAÇÃO DOS CAMPOS OBRIGATÓRIOS
            // ===================================
            
            function validarCamposCardAzul() {
                const camposObrigatorios = [
                    'nome_item',
                    'categoria', 
                    'descricao',
                    'quantidade',
                    'justificativa_pedido'
                ];
                
                let todosPreenchidos = true;
                let primeiroErro = null;
                
                camposObrigatorios.forEach(campo => {
                    const elemento = document.querySelector(`[name="${campo}"]`);
                    if (elemento && !elemento.value.trim()) {
                        todosPreenchidos = false;
                        elemento.classList.add('is-invalid');
                        
                        // Adicionar efeito shake
                        elemento.classList.add('shake');
                        setTimeout(() => {
                            elemento.classList.remove('shake');
                        }, 820);
                        
                        if (!primeiroErro) {
                            primeiroErro = elemento;
                        }
                    } else if (elemento) {
                        elemento.classList.remove('is-invalid');
                    }
                });
                
                // Focar no primeiro campo com erro
                if (primeiroErro) {
                    primeiroErro.focus();
                    primeiroErro.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
                
                return todosPreenchidos;
            }
            
            // ===================================
            // EVENTO DO BOTÃO PRÓXIMO
            // ===================================
            
            if (btnProximo && isCreating) {
                btnProximo.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    if (validarCamposCardAzul()) {
                        // Desbloquear os cards com animação
                        desbloquearCards();
                        
                        // Esconder botão próximo com animação
                        btnProximo.style.transition = 'all 0.3s ease';
                        btnProximo.style.opacity = '0';
                        btnProximo.style.transform = 'translateY(-10px)';
                        
                        setTimeout(() => {
                            btnProximo.style.display = 'none';
                        }, 300);
                        
                        // Mostrar botão salvar e enviar com animação
                        if (btnSalvarEnviar) {
                            setTimeout(() => {
                                btnSalvarEnviar.style.display = 'inline-block';
                                btnSalvarEnviar.style.opacity = '0';
                                btnSalvarEnviar.style.transform = 'translateY(10px)';
                                
                                setTimeout(() => {
                                    btnSalvarEnviar.style.transition = 'all 0.3s ease';
                                    btnSalvarEnviar.style.opacity = '1';
                                    btnSalvarEnviar.style.transform = 'translateY(0)';
                                }, 50);
                            }, 800);
                        }
                        
                        // Scroll suave para os cards desbloqueados
                        setTimeout(() => {
                            const cardAmarelo = document.getElementById('card-amarelo');
                            if (cardAmarelo) {
                                cardAmarelo.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'start'
                                });
                            }
                        }, 1200);
                        
                        // Mostrar notificação de sucesso
                        mostrarNotificacao('Cards desbloqueados! Agora você pode preencher todos os campos.', 'success');
                        
                    } else {
                        // Mostrar notificação de erro
                        mostrarNotificacao('Por favor, preencha todos os campos obrigatórios do card azul antes de continuar.', 'error');
                    }
                });
            }
            
            // ===================================
            // FUNÇÃO DE NOTIFICAÇÃO
            // ===================================
            
            function mostrarNotificacao(mensagem, tipo = 'info') {
                // Remover notificação existente
                const notificacaoExistente = document.querySelector('.notificacao-ppp');
                if (notificacaoExistente) {
                    notificacaoExistente.remove();
                }
                
                // Criar nova notificação
                const notificacao = document.createElement('div');
                notificacao.className = `alert alert-${tipo === 'success' ? 'success' : 'danger'} alert-dismissible fade show notificacao-ppp`;
                notificacao.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    min-width: 300px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                `;
                
                notificacao.innerHTML = `
                    <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'} mr-2"></i>
                    ${mensagem}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                `;
                
                document.body.appendChild(notificacao);
                
                // Auto remover após 5 segundos
                setTimeout(() => {
                    if (notificacao.parentNode) {
                        notificacao.remove();
                    }
                }, 5000);
            }
            
            // ===================================
            // CAMPOS CONDICIONAIS
            // ===================================
            
            // Contrato Vigente
            const temContratoVigente = document.getElementById('tem_contrato_vigente');
            const camposContratoVigente = document.getElementById('campos-contrato-vigente');
            
            if (temContratoVigente && camposContratoVigente) {
                function toggleCamposContrato() {
                    if (temContratoVigente.value === 'Sim') {
                        camposContratoVigente.style.display = 'block';
                        camposContratoVigente.style.animation = 'fadeInUp 0.3s ease';
                    } else {
                        camposContratoVigente.style.display = 'none';
                    }
                }
                
                temContratoVigente.addEventListener('change', toggleCamposContrato);
                toggleCamposContrato(); // Executar na inicialização
            }
            
            // Vinculação/Dependência
            const vinculacaoItem = document.getElementById('vinculacao_item');
            const camposVinculacao = document.getElementById('campos-vinculacao');
            
            if (vinculacaoItem && camposVinculacao) {
                function toggleCamposVinculacao() {
                    if (vinculacaoItem.value === 'Sim') {
                        camposVinculacao.style.display = 'block';
                        camposVinculacao.style.animation = 'fadeInUp 0.3s ease';
                    } else {
                        camposVinculacao.style.display = 'none';
                    }
                }
                
                vinculacaoItem.addEventListener('change', toggleCamposVinculacao);
                toggleCamposVinculacao(); // Executar na inicialização
            }
            
            // ===================================
            // MÁSCARAS E FORMATAÇÃO
            // ===================================
            
            // Máscara para valores monetários
            const camposMonetarios = document.querySelectorAll('.money-field');
            camposMonetarios.forEach(campo => {
                campo.addEventListener('input', function(e) {
                    let valor = e.target.value.replace(/\D/g, '');
                    valor = (valor / 100).toFixed(2) + '';
                    valor = valor.replace('.', ',');
                    valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    e.target.value = 'R$ ' + valor;
                });
            });
            
            // Contador de caracteres para textareas
            const textareasComContador = document.querySelectorAll('textarea[maxlength]');
            textareasComContador.forEach(textarea => {
                const maxLength = textarea.getAttribute('maxlength');
                const contador = document.createElement('div');
                contador.className = 'char-counter';
                contador.innerHTML = `<span class="current">0</span>/${maxLength} caracteres`;
                textarea.parentNode.appendChild(contador);
                
                textarea.addEventListener('input', function() {
                    const current = this.value.length;
                    const currentSpan = contador.querySelector('.current');
                    currentSpan.textContent = current;
                    
                    if (current > maxLength * 0.9) {
                        contador.classList.add('text-warning');
                    } else {
                        contador.classList.remove('text-warning');
                    }
                    
                    if (current >= maxLength) {
                        contador.classList.add('text-danger');
                    } else {
                        contador.classList.remove('text-danger');
                    }
                });
                
                // Trigger inicial
                textarea.dispatchEvent(new Event('input'));
            });
            
            // ===================================
            // VALIDAÇÃO EM TEMPO REAL
            // ===================================
            
            // Remover classe de erro quando o usuário começar a digitar
            const camposComValidacao = document.querySelectorAll('input[required], select[required], textarea[required]');
            camposComValidacao.forEach(campo => {
                campo.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                });
                
                campo.addEventListener('change', function() {
                    if (this.value.trim()) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                });
            });
            
            // ===================================
            // BOTÃO CANCELAR
            // ===================================
            
            if (btnCancelar) {
                btnCancelar.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    if (confirm('Tem certeza que deseja cancelar? Todas as alterações não salvas serão perdidas.')) {
                        window.location.href = '{{ route("ppp.index") }}';
                    }
                });
            }
            
            // ===================================
            // PREVENÇÃO DE PERDA DE DADOS
            // ===================================
            
            let formAlterado = false;
            const formulario = document.querySelector('form');
            
            if (formulario) {
                // Monitorar mudanças no formulário
                formulario.addEventListener('input', function() {
                    formAlterado = true;
                });
                
                formulario.addEventListener('change', function() {
                    formAlterado = true;
                });
                
                // Avisar antes de sair da página
                window.addEventListener('beforeunload', function(e) {
                    if (formAlterado && isCreating) {
                        e.preventDefault();
                        e.returnValue = 'Você tem alterações não salvas. Tem certeza que deseja sair?';
                        return e.returnValue;
                    }
                });
                
                // Não avisar ao submeter o formulário
                formulario.addEventListener('submit', function() {
                    formAlterado = false;
                });
            }
            
            // ===================================
            // INICIALIZAÇÃO FINAL
            // ===================================
            
            console.log('🚀 PPP Form JavaScript inicializado com sucesso!');
            console.log('📝 Modo:', isCreating ? 'Criação' : 'Edição');
            
            // Se não estiver criando, mostrar todos os cards desbloqueados
            if (!isCreating) {
                const cardsParaDesbloquear = document.querySelectorAll('.card-bloqueado.bloqueado');
                cardsParaDesbloquear.forEach(card => {
                    card.classList.remove('bloqueado');
                });
            }
        });
    </script>
@endsection