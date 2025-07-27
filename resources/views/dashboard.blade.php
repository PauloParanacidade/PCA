@extends('adminlte::page')

@section('title', 'Bem-vindo')

@section('content_header')
    <h1>Bem-vindo(a), {{ Auth::user()->name }} - {{ Auth::user()->setor ?? 'Setor não informado' }}</h1>
@stop

@section('content')
<div class="row">

    {{-- Card de Boas-vindas e resumo --}}
    <div class="col-md-12">
        <x-adminlte-callout theme="info" title="Sistema PCA - Planejamento de Contratações Anual">
            Este sistema auxilia na gestão de pedidos de planejamento de compras e contratações públicas.
            Utilize o menu lateral para iniciar um novo pedido (PPP), consultar status, aprovar solicitações e muito mais.

            <hr>

            <p><strong>PPPs para avaliar:</strong> {{ $pppsParaAvaliar ?? 0 }}</p>
            <p><strong>PPPs criados por você:</strong> {{ $pppsMeus ?? 0 }}</p>
        </x-adminlte-callout>
    </div>

    {{-- Cards de Acesso Rápido --}}
    <div class="col-md-4">
        <a href="{{ route('ppp.create') }}" class="info-box-link">
            <x-adminlte-info-box 
                title="Novo PPP" 
                text="Inicie criando uma novo PPP (Proposta para o PCA)" 
                icon="fas fa-plus-circle" 
                icon-theme="green" />
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('ppp.index') }}" class="info-box-link">
            <x-adminlte-info-box 
                title="Para Avaliar" 
                text="Pedidos aguardando sua análise" 
                icon="fas fa-user-check" 
                icon-theme="warning" />
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('ppp.meus') }}" class="info-box-link">
            <x-adminlte-info-box 
                title="Meus PPPs" 
                text="Acompanhe seus pedidos" 
                icon="fas fa-list" 
                icon-theme="primary" />
        </a>
    </div>

    {{-- Card de Status do Sistema --}}
    <div class="col-md-12 mt-4">
        <x-adminlte-callout theme="success" title="Status do Sistema">
            Última atualização: {{ now()->format('d/m/Y H:i') }} <br>
            Todos os módulos estão operacionais.
        </x-adminlte-callout>
    </div>

</div>
@stop
