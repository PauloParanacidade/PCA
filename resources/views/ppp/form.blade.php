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
            <div class="row mb-4">
                {{-- Card Azul - Sempre visível --}}
                <div class="col-lg-6">
                    @include('ppp.partials.informacoes-item')
                </div>
                
                {{-- Card Amarelo - Visível após clicar em Próximo (criação) ou sempre (edição) --}}
                <div class="col-lg-6" id="card-amarelo" style="{{ $isCreating ? 'display: none;' : 'display: block;' }}">
                    @include('ppp.partials.contrato-vigente')
                </div>
            </div>

            {{-- Segunda linha: Card Verde e Card Ciano --}}
            <div id="cards-adicionais" style="{{ $isCreating ? 'display: none;' : 'display: block;' }}">
                <div class="row mb-4">
                    {{-- Card Verde --}}
                    <div class="col-lg-6">
                        @include('ppp.partials.informacoes-financeiras')
                    </div>
                    
                    {{-- Card Ciano --}}
                    <div class="col-lg-6">
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
            const btnProximo = document.getElementById('btn-proximo-card-azul');
            const btnSalvarEnviar = document.getElementById('btn-salvar-enviar');
            const cardAmarelo = document.getElementById('card-amarelo');
            const cardsAdicionais = document.getElementById('cards-adicionais');

            if (btnProximo) {
                btnProximo.addEventListener('click', function() {
                    // Validar campos obrigatórios do card azul
                    const camposObrigatorios = [
                        'nome_item',
                        'quantidade',
                        'categoria',
                        'grau_prioridade',
                        'previsao_contratacao',
                        'descricao_especificacao'
                    ];

                    let todosPreenchidos = true;
                    let primeiroErro = null;

                    camposObrigatorios.forEach(function(campo) {
                        const elemento = document.querySelector(`[name="${campo}"]`);
                        if (elemento && !elemento.value.trim()) {
                            elemento.classList.add('is-invalid');
                            if (!primeiroErro) {
                                primeiroErro = elemento;
                            }
                            todosPreenchidos = false;
                        } else if (elemento) {
                            elemento.classList.remove('is-invalid');
                        }
                    });

                    if (todosPreenchidos) {
                        // Mostrar card amarelo com animação
                        if (cardAmarelo) {
                            cardAmarelo.style.display = 'block';
                            cardAmarelo.classList.add('fade-in-cards');
                        }

                        // Mostrar cards adicionais com animação
                        if (cardsAdicionais) {
                            cardsAdicionais.style.display = 'block';
                            cardsAdicionais.classList.add('fade-in-cards');
                        }

                        // Esconder botão próximo
                        btnProximo.style.display = 'none';

                        // Mostrar botão salvar e enviar
                        if (btnSalvarEnviar) {
                            btnSalvarEnviar.style.display = 'inline-block';
                        }

                        // Scroll suave para os novos cards
                        setTimeout(() => {
                            if (cardAmarelo) {
                                cardAmarelo.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'start'
                                });
                            }
                        }, 300);
                    } else {
                        // Focar no primeiro campo com erro
                        if (primeiroErro) {
                            primeiroErro.focus();
                        }

                        // Mostrar alerta de campos obrigatórios
                        Swal.fire({
                            icon: 'warning',
                            title: 'Campos obrigatórios',
                            text: 'Por favor, preencha todos os campos obrigatórios antes de continuar.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#007bff'
                        });
                    }
                });
            }

            // Remover classe de erro quando o usuário começar a digitar
            document.querySelectorAll('input, select, textarea').forEach(function(elemento) {
                elemento.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                });

                elemento.addEventListener('change', function() {
                    this.classList.remove('is-invalid');
                });
            });
        });
    </script>
@endsection