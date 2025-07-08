@extends('layouts.adminlte-custom')

@php
    use Carbon\Carbon;
@endphp

@php
    $edicao = isset($ppp);
@endphp

@section('title', $edicao ? 'Editar PPP' : 'Criar novo PPP')

@section('page_header')
    <div class="row align-items-center mb-4">
        <div class="col-12 text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
                <div>
                    <h1 class="fw-bold mb-0 text-primary" style="font-size: 2.5rem;">PPP</h1>
                    <small class="text-muted" style="font-size: 1.1rem;">Proposta para PCA</small>
                </div>
            </div>
            <h4 class="mb-0 text-secondary">{{ $edicao ? 'Editar PPP' : 'Criar novo PPP' }}</h4>
        </div>
    </div>
@endsection

@section('content')

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h6><i class="fas fa-exclamation-triangle me-2"></i><strong>Existem erros no formulário:</strong></h6>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form method="POST" action="{{ $edicao ? route('ppp.update', $ppp->id) : route('ppp.store') }}">
    @csrf
    @if ($edicao)
        @method('PUT')
    @endif

    <div class="row">
        @include('ppp.partials.informacoes-item')
        
        @include('ppp.partials.contrato-vigente')
        
        @include('ppp.partials.informacoes-financeiras')
        
        @include('ppp.partials.vinculacao-dependencia')
    </div>



@include('ppp.partials.botoes-acao')
</form>

@endsection

{{-- Incluir CSS e JS específicos do PPP --}}
@vite(['resources/css/ppp-form.css', 'resources/js/ppp-form.js'])

<script src="{{ asset('/js/maskMoney.js') }}"></script>