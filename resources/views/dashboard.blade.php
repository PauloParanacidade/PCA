@extends('layouts.adminlte-custom')

@section('title', 'Bem-vindo')

@section('content_header_content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center flex-wrap">
        <h1 class="mb-2 mb-md-0">Bem-vindo(a), <strong>{{ Auth::user()->name }}</strong></h1>
        <small class="text-muted font-italic">{{ Auth::user()->department ?? 'Setor não informado' }}</small>
    </div>
@stop

@section('content')
<div class="container-fluid">

    {{-- Mensagem PCA com destaque --}}
    <div class="card shadow-sm mb-4 border-primary">
        <div class="card-body">
            <h3 class="card-title font-weight-bold text-primary">Sistema PCA - Planejamento de Contratações Anual</h3>
            <p class="card-text text-secondary">
                Este sistema auxilia na gestão de pedidos de planejamento de compras e contratações públicas.
                Utilize o menu lateral para acassar a página inicial (home), iniciar um novo PPP (Projeção para PCA), consultar status dos seus PPPs e aprovar solicitações, se você for um gestor.
            </p>
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

    {{-- Segunda linha de cards --}}
    <div class="row mb-4">
        <div class="col-12 col-md-6 mb-3 mb-md-0">
            <a href="{{ route('ppp.acompanhar') }}" class="info-box-link d-block" title="Clique para acompanhar PPPs da sua área">
                <x-adminlte-info-box 
                    title="PPPs para Acompanhar ({{ $pppsAcompanhar ?? 0 }})" 
                    text="Clique para acompanhar PPPs da sua área" 
                    icon="fas fa-eye" 
                    icon-theme="info" />
            </a>
        </div>
        
        <div class="col-12 col-md-6">
            {{-- Espaço reservado para futuras funcionalidades --}}
            <div class="info-box-disabled">
                <x-adminlte-info-box 
                    title="Relatórios" 
                    text="Em desenvolvimento" 
                    icon="fas fa-chart-bar" 
                    icon-theme="secondary" />
            </div>
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
</style>
@stop
