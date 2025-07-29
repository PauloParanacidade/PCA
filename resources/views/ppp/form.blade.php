@extends('layouts.adminlte-custom')

@section('title', $isCreating ? 'Criar PPP' : 'Editar PPP')

@section('content_header')
    @parent
    {{-- Remover o debug dump após confirmar funcionamento --}}
    @if($errors->any() || session('error'))
        <div class="alert alert-danger">
            @if(session('error'))
                <p>{{ session('error') }}</p>
            @endif
            @if($errors->any())
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
    <h1><i class="fas fa-plus-circle mr-2"></i>{{ $isCreating ? 'Criar novo PPP' : 'Editar PPP' }}</h1>
@endsection

@section('content')
    <form id="form-ppp" method="POST" action="{{ $isCreating ? route('ppp.store') : route('ppp.update', $ppp->id) }}">
    @csrf
    @if(!$isCreating)
        @method('PUT')
    @endif
        <input type="hidden" name="modo" value="{{ $isCreating ? 'criacao' : 'edicao' }}">
        
        {{-- Adicionar campo hidden com ID para JavaScript --}}
        @if(!$isCreating)
            <input type="hidden" id="ppp-id" value="{{ $ppp->id }}">
        @endif

        <div class="row mb-4 align-items-stretch">
            {{-- Lado esquerdo: Azul ocupa metade da linha --}}
            <div class="col-lg-6 d-flex">
                <div class="w-100">
                    @include('ppp.partials.informacoes-item')
                </div>
            </div>

            {{-- Lado direito: Amarelo + Verde + Ciano empilhados --}}
            <div class="col-lg-6">
                <div class="row h-100">
                    {{-- Card Amarelo --}}
                    <div class="col-6 d-flex mb-3">
                        <div id="card-amarelo" class="{{ $isCreating ? 'card-bloqueado bloqueado' : '' }} w-100">
                            @include('ppp.partials.contrato-vigente')
                        </div>
                    </div>

                    {{-- Card Verde --}}
                    <div class="col-6 d-flex mb-3">
                        <div id="card-verde" class="{{ $isCreating ? 'card-bloqueado bloqueado' : '' }} w-100">
                            @include('ppp.partials.informacoes-financeiras')
                        </div>
                    </div>

                    {{-- Card Ciano --}}
                    <div class="col-12 d-flex mb-3">
                        <div id="card-ciano" class="{{ $isCreating ? 'card-bloqueado bloqueado' : '' }} w-100">
                            @include('ppp.partials.vinculacao-dependencia')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Incluir botões da partial --}}
        @include('ppp.partials.botoes-acao')
    </form>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/ppp-form.css') }}">
<style>
    .content-wrapper,
    .content,
    .container-fluid {
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }

    .card-body {
        padding-bottom: 0.5rem !important;
    }

    #btn-avancar-card-azul {
        margin-bottom: 0 !important;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        transition: all 0.3s ease;
    }

    #btn-avancar-card-azul:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
    }

    /* Animação */
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

    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }

    .shake {
        animation: shake 0.4s;
    }

    @keyframes shake {
        0% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        50% { transform: translateX(5px); }
        75% { transform: translateX(-5px); }
        100% { transform: translateX(0); }
    }

    .bloqueado {
        display: none !important;
    }

    @media (max-width: 768px) {
        .col-lg-6 {
            margin-bottom: 1rem;
        }

        #btn-avancar-card-azul {
            width: 100%;
            margin-top: 1rem;
        }
    }

    .card {
        height: 100%;
    }

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
            const btnAvancar = document.getElementById('btn-avancar-card-azul');
            const btnSalvarEnviar = document.getElementById('btn-salvar-enviar');
            // const btnCancelar = document.getElementById('btn-cancelar');
            const form = document.getElementById('form-ppp');

            // ===================================
            // FUNÇÃO PARA DEFINIR O STORE PARA AVANÇAR E UPDATE PARA SALVAR E ENVIAR PARA AVALIAÇÃO
            // ===================================
            
            btnAvancar.addEventListener('click', function(e) {
                e.preventDefault();

                if (!validarCamposCardAzul()) {
                    mostrarNotificacao('Por favor, preencha todos os campos obrigatórios do card azul antes de continuar.', 'error');
                    return;
                }

                if (isCreating) {
                    // Criar FormData com os dados do formulário
                    const formData = new FormData(form);
                    formData.append('acao', 'salvar_rascunho');
                    
                    // Fazer requisição AJAX para salvar o rascunho
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Atualizar a URL para o modo de edição
                            const newUrl = `{{ route('ppp.edit', ':id') }}`.replace(':id', data.ppp_id);
                            window.history.replaceState({}, '', newUrl);
                            
                            // Atualizar a action do formulário para update
                            form.action = `{{ route('ppp.update', ':id') }}`.replace(':id', data.ppp_id);
                            
                            // Adicionar método PUT
                            let methodInput = form.querySelector('input[name="_method"]');
                            if (!methodInput) {
                                methodInput = document.createElement('input');
                                methodInput.type = 'hidden';
                                methodInput.name = '_method';
                                methodInput.value = 'PUT';
                                form.appendChild(methodInput);
                            }
                            
                            // ✅ CORREÇÃO: Atualizar o campo modo para 'edicao'
                            let inputModo = form.querySelector('input[name="modo"]');
                            if (inputModo) {
                                inputModo.value = 'edicao';
                            }
                            
                            // Alterar ação para enviar_aprovacao
                            let inputAcao = form.querySelector('input[name="acao"]');
                            if (inputAcao) inputAcao.remove();
                            
                            inputAcao = document.createElement('input');
                            inputAcao.type = 'hidden';
                            inputAcao.name = 'acao';
                            inputAcao.value = 'enviar_aprovacao';
                            form.appendChild(inputAcao);
                            
                            // Desbloquear os cards
                            desbloquearCards();
                    
                            btnAvancar.style.transition = 'all 0.3s ease';
                            btnAvancar.style.opacity = '0';
                            btnAvancar.style.transform = 'translateY(-10px)';
                            setTimeout(() => {
                                btnAvancar.style.display = 'none';
                            }, 300);
                    
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
                    
                            setTimeout(() => {
                                const cardAmarelo = document.getElementById('card-amarelo');
                                if (cardAmarelo) {
                                    cardAmarelo.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                }
                            }, 1200);
                    
                            mostrarNotificacao('Rascunho salvo com sucesso!', 'success');
                        } else {
                            mostrarNotificacao('Erro ao salvar rascunho: ' + (data.message || 'Erro desconhecido'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erro na requisição:', error);
                        mostrarNotificacao('Erro ao salvar rascunho. Tente novamente.', 'error');
                    });
                } 
            });


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
                let corBootstrap;

                switch (tipo) {
                    case 'success':
                        corBootstrap = 'success';
                        break;
                    case 'warning':
                        corBootstrap = 'warning';
                        break;
                    case 'info':
                        corBootstrap = 'info';
                        break;
                    case 'secondary':
                        corBootstrap = 'secondary';
                        break;
                    case 'gray':
                    case 'gray-dark':
                        corBootstrap = 'dark'; // Personalizável com CSS
                        break;
                    default:
                        corBootstrap = 'danger';
                        break;
                }

                notificacao.className = `alert alert-${corBootstrap} alert-dismissible fade show notificacao-ppp`;

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

{{-- Mudar de @push('js') para @section('page_js') --}}
@section('page_js')
    @vite('resources/js/ppp-form.js')
@endsection
