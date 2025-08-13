@extends('layouts.adminlte-custom')

@section('title', 'Bem-vindo')

@section('content_header_content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center flex-wrap">
        <h1 class="mb-2 mb-md-0">
            Bem-vindo(a), <strong>{{ Auth::user()->name }}</strong>
            <span class="text-muted font-italic" style="font-size: 0.85em;"> - {{ Auth::user()->department ?? 'Setor não informado' }}</span>
        </h1>
    </div>
@stop

@section('content')
<div class="container-fluid">

    {{-- Informações do Sistema --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="modern-info-card">
                <div class="d-flex align-items-center mb-4">
                    <div class="icon-wrapper mr-4">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                    <div>
                        <h1 class="mb-1 font-weight-bold text-white" style="font-size: 3.125rem;">Sistema PCA - Planejamento de Contratações Anual</h1>
                        <p class="mb-0 text-white font-weight-medium" style="opacity: 0.9; font-size: 1.25rem;">Gestão Inteligente de Pedidos de Planejamento de Compras e Contratações Públicas</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-7">
                        <div class="info-section-compact mb-3">
                            <h2 class="section-title" style="font-size: 2.25rem;">
                                <i class="fas fa-lightbulb mr-2"></i>O que é PPP?
                            </h2>
                            <p class="section-content mb-2" style="font-size: 1.375rem; line-height: 1.7;">
                                <strong class="text-primary">PPP (Projeção para PCA)</strong> é um documento que formaliza a solicitação de itens para o 
                                Plano de Contratações Anual. Através deste sistema, você pode criar, acompanhar e gerenciar 
                                suas solicitações de compras e contratações de forma eficiente e organizada.
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <div class="info-section-compact mb-3">
                            <h6 class="section-title" style="font-size: 1.25rem;">
                                <i class="fas fa-rocket mr-2"></i>Como utilizar:
                            </h6>
                            <ul class="modern-list mb-2" style="font-size: 1.125rem;">
                                <li><i class="fas fa-chevron-right text-white mr-2"></i>Use o menu lateral para navegar entre as seções</li>
                                <li><i class="fas fa-chevron-right text-white mr-2"></i>Inicie um novo PPP clicando em "Novo PPP"</li>
                                <li><i class="fas fa-chevron-right text-white mr-2"></i>Consulte o status dos seus PPPs em "Meus PPPs"</li>
                                <li><i class="fas fa-chevron-right text-white mr-2"></i>Gestores podem aprovar solicitações em "Para Avaliar"</li>
                                <li><i class="fas fa-chevron-right text-white mr-2"></i>Gestores também podem ter uma visão geral do andamento dos PPPs de sua área</li>
                            </ul>
                        </div>
                        
                        <div class="development-card-compact mt-3">
                            <div class="dev-header">
                                <i class="fas fa-code mr-2"></i>
                                <span class="dev-title">Em Desenvolvimento</span>
                            </div>
                            <div class="dev-content">
                                <h6 class="mb-1 font-weight-bold" style="font-size: 1.25rem;">DFD - Documento de Formalização da Demanda</h6>
                                <p class="mb-1" style="font-size: 1rem; color: rgba(255, 255, 255, 0.85); font-weight: 500;">
                                    Esta funcionalidade está sendo desenvolvida e estará disponível na segunda fase de implementação do sistema.
                                </p>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-gradient-secondary" role="progressbar" style="width: 18%" aria-valuenow="18" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Título Acesso Rápido --}}
    <div class="mb-3">
        <h4 class="font-weight-bold text-secondary border-left border-primary pl-3" style="border-width: 4px !important;">
            Acesso Rápido:
        </h4>
    </div>

    {{-- Cards de Acesso Rápido --}}
    <div class="row mb-3">
        <div class="col-12 col-md-4 mb-3 mb-md-0">
            <a href="{{ route('ppp.create') }}" class="info-box-link d-block" title="Clique para iniciar um novo PPP">
                <x-adminlte-info-box 
                    title="Novo PPP" 
                    text="Clique para iniciar um novo PPP" 
                    icon="fas fa-plus-circle" 
                    icon-theme="green" />
            </a>
        </div>

        @php
        $usuarioLogado = auth()->user();
        $podeAvaliar = $usuarioLogado->hasAnyRole(['admin', 'daf', 'gestor', 'secretaria']);
        @endphp

        <div class="col-12 col-md-4 mb-3 mb-md-0">
            @if($podeAvaliar)
                <a href="{{ route('ppp.index') }}" class="info-box-link d-block" title="Clique para ver PPPs para avaliar">
                    <x-adminlte-info-box 
                        title="Para Avaliar ({{ $pppsParaAvaliar ?? 0 }})" 
                        text="Clique para ver PPPs para avaliar" 
                        icon="fas fa-user-check" 
                        icon-theme="warning" />
                </a>
            @else
                <div class="info-box-disabled">
                    <x-adminlte-info-box 
                        title="Para Avaliar (—)" 
                        text="Sem permissão para avaliar" 
                        icon="fas fa-user-check" 
                        icon-theme="secondary" />
                </div>
            @endif
        </div>

        <div class="col-12 col-md-4">
            <a href="{{ route('ppp.meus') }}" class="info-box-link d-block" title="Clique para ver seus PPPs">
                <x-adminlte-info-box 
                    title="Meus PPPs ({{ $pppsMeus ?? 0 }})" 
                    text="Clique para ver seus PPPs" 
                    icon="fas fa-list" 
                    icon-theme="primary" />
            </a>
        </div>
    </div>



    {{-- Status do Sistema --}}
    {{-- <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title text-success font-weight-bold">Status do Sistema</h5>
            <p class="card-text mb-0">
                Última atualização do código: <strong>{{ $ultimoCommit ?? 'Data desconhecida' }}</strong>
            </p>
        </div>
    </div> --}}
</div>
@stop

@section('css')
<style>
    /* Cursor pointer para o card inteiro */
    a.info-box-link:hover {
        filter: brightness(0.93);
        text-decoration: none;
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    
    /* Estilo para elementos desabilitados */
    .info-box-disabled {
        opacity: 0.6;
        cursor: not-allowed;
        pointer-events: none;
    }
    
    .info-box-disabled .info-box {
        background-color: #f8f9fa !important;
        border: 1px dashed #dee2e6;
    }

    /* Estilos modernos para o card principal */
    .modern-info-card {
        background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 30%, #4338ca 70%, #3b82f6 100%);
        background-size: 300% 300%;
        animation: gradientShift 12s ease infinite;
        border-radius: 20px;
        padding: 2rem;
        color: white;
        box-shadow: 0 8px 25px rgba(30, 58, 138, 0.4);
        border: none;
        position: relative;
        overflow: hidden;
    }

    .modern-info-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(225deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
        pointer-events: none;
    }

    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        25% { background-position: 50% 25%; }
        50% { background-position: 100% 50%; }
        75% { background-position: 50% 75%; }
        100% { background-position: 0% 50%; }
    }

    .icon-wrapper {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .icon-wrapper i {
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .modern-info-card h4 {
        color: white !important;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .info-section {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 1.5rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .info-section-compact {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 1rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        height: fit-content;
    }

    .section-title {
        color: #fff !important;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
    }

    .section-title i {
        color: #ffd700;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
    }

    .section-content {
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.6;
    }

    .section-content strong {
        color: #ffd700 !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }

    .modern-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .modern-list li {
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 0.8rem;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .modern-list li:hover {
        transform: translateX(5px);
        color: white;
    }

    .modern-list li i {
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.3));
    }

    .development-card {
        background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 8px 25px rgba(255, 154, 158, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
        position: relative;
        overflow: hidden;
    }

    .development-card-compact {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 1rem;
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
        position: relative;
        overflow: hidden;
        width: 100%;
        height: fit-content;
        opacity: 0.85;
    }

    /* Shimmer effect removido conforme solicitado */

    .dev-header {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }

    .dev-title {
        font-weight: 600;
        color: #fff;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .dev-header i {
        color: #ffd700;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
    }

    .dev-content h6 {
        color: #fff;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .dev-content p {
        color: rgba(255, 255, 255, 0.9);
    }

    /* Melhorias nos cards de acesso rápido */
    .info-box {
        border-radius: 15px !important;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
        transition: all 0.3s ease !important;
    }

    .info-box:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }

    /* Gradientes para os ícones dos cards */
    .info-box .info-box-icon {
        border-radius: 15px 0 0 15px !important;
    }

    .bg-green {
        background: linear-gradient(135deg, #00b894, #00cec9) !important;
    }

    .bg-warning {
        background: linear-gradient(135deg, #fdcb6e, #e17055) !important;
    }

    .bg-primary {
        background: linear-gradient(135deg, #74b9ff, #0984e3) !important;
    }

    /* Animação suave para o título */
    h4.border-left {
        position: relative;
        overflow: hidden;
    }

    h4.border-left::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        transition: width 0.3s ease;
    }

    h4.border-left:hover::after {
        width: 100%;
    }
</style>
@stop
