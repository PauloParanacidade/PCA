@extends('layouts.adminlte-custom')

@php
    use Carbon\Carbon;
@endphp

@php
    $edicao = isset($ppp);
@endphp

@section('title', $edicao ? 'Editar PPP' : 'Criar novo PPP')

@section('content_header')
    {{-- Teste direto do componente --}}
    <div style="background: purple; color: white; padding: 10px; margin: 10px 0;">
        <strong>TESTE DIRETO:</strong> Esta div deveria aparecer sempre!
    </div>
    
    {{-- Incluir o banner diretamente --}}
    <x-impersonate-banner />
    
    {{-- Conteúdo original da página --}}
    <div class="text-center mb-0">
        <h1 class="fw-bold" style="font-size: 3rem;">{{ $edicao ? 'Editar PPP' : 'PPP' }}</h1>
        <small class="text-muted" style="font-size: 1rem;">Proposta para PCA</small>
    </div>
@endsection

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                {{ $edicao ? 'Atualize as informações do PPP' : 'Formulário a ser aprovado pelo coordenador da área' }}
            </h3>
        </div>
        <form method="POST" action="{{ $edicao ? route('ppp.update', $ppp->id) : route('ppp.store') }}">
            @csrf
            @if ($edicao)
                @method('PUT')
            @endif

            <div class="card-body">
                {{-- 1ª Linha --}}
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Área Solicitante</label>
                        <input type="text" class="form-control" name="area_solicitante"
                            value="{{ old('area_solicitante', $ppp->area_solicitante ?? auth()->user()->area_solicitante_formatada) }}"
                            readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Aprovador/Avaliador</label>
                        <input type="text" class="form-control" name="area_responsavel"
                            value="{{ old('area_responsavel', $ppp->area_responsavel ?? auth()->user()->area_responsavel_formatada) }}"
                            readonly>
                    </div>
                </div>

                {{-- 2ª Linha --}}
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Categoria</label>
                        <select class="form-control" name="categoria" required>
                            <option value="" disabled
                                {{ old('categoria', $ppp->categoria ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                            @foreach (['Aquisição de bens', 'Contratação de Serviço', 'Obras', 'T.I.'] as $cat)
                                <option value="{{ $cat }}"
                                    {{ old('categoria', $ppp->categoria ?? '') == $cat ? 'selected' : '' }}>
                                    {{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-5">
                        <label>Nome do Item</label>
                        <input type="text" name="nome_item" class="form-control" required
                            value="{{ old('nome_item', $ppp->nome_item ?? '') }}"
                            placeholder="Ex: Aluguel de impressoras ou Consultoria para suporte em TI" autocomplete="off">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Quantidade</label>
                        <input type="text" name="quantidade" class="form-control" required
                            value="{{ old('quantidade', $ppp->quantidade ?? '') }}"
                            placeholder="Ex: 2 unidades ou 1 visita por ano" autocomplete="off">
                    </div>
                </div>

                {{-- 3ª Linha --}}
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Descrição do objeto</label>
                        <textarea name="descricao" class="form-control" rows="4" required>{{ old('descricao', $ppp->descricao ?? '') }}</textarea>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Justificativa para aquisição do item</label>
                        <textarea name="justificativa_pedido" class="form-control" rows="4" required>{{ old('justificativa_pedido', $ppp->justificativa_pedido ?? '') }}</textarea>
                    </div>
                </div>

                {{-- 4ª Linha --}}
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label>Estimativa de Valor para o período</label>
                        <input type="text" name="estimativa_valor" class="form-control estimativa_valor" required
                            value="{{ old('estimativa_valor', $ppp->estimativa_valor ?? '') }}" placeholder="R$ 0,00"
                            autocomplete="off">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Origem do Recurso</label>
                        <select class="form-control" name="origem_recurso" required>
                            <option value="" disabled
                                {{ old('origem_recurso', $ppp->origem_recurso ?? '') == '' ? 'selected' : '' }}>Selecione
                            </option>
                            @foreach (['Paranacidade', 'BID/FDU', 'FDU'] as $origem)
                                <option value="{{ $origem }}"
                                    {{ old('origem_recurso', $ppp->origem_recurso ?? '') == $origem ? 'selected' : '' }}>
                                    {{ $origem }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Fonte justificativa do valor</label>
                        <input type="text" name="justificativa_valor" class="form-control" required
                            value="{{ old('justificativa_valor', $ppp->justificativa_valor ?? '') }}"
                            placeholder="Cotação realizada dia 01/01/2025 no portal do governo. Índice de aumento x % conforme indicador y" autocomplete="off">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Prioridade</label>
                        <select class="form-control" name="grau_prioridade" required>
                            <option value="" disabled
                                {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == '' ? 'selected' : '' }}>Selecione
                            </option>
                            @foreach (['Alta', 'Média', 'Baixa'] as $nivel)
                                <option value="{{ $nivel }}"
                                    {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == $nivel ? 'selected' : '' }}>
                                    {{ $nivel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 5ª Linha --}}
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label>Vinculação/Dependência</label>
                        <select name="vinculacao_item" class="form-control" required>
                            <option value="" disabled
                                {{ old('vinculacao_item', $ppp->vinculacao_item ?? '') == '' ? 'selected' : '' }}>Selecione
                            </option>
                            <option value="Sim"
                                {{ old('vinculacao_item', $ppp->vinculacao_item ?? '') == 'Sim' ? 'selected' : '' }}>Sim
                            </option>
                            <option value="Não"
                                {{ old('vinculacao_item', $ppp->vinculacao_item ?? '') == 'Não' ? 'selected' : '' }}>Não
                            </option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Justificativa da Vinculação/Dependência com outro item</label>
                        <input type="text" name="justificativa_vinculacao" class="form-control"
                            value="{{ old('justificativa_vinculacao', $ppp->justificativa_vinculacao ?? '') }}"
                            placeholder="Identificar a qual item o pedido está vinculado e justificar" autocomplete="off">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Data Ideal para Contratação</label>
                        <div class="d-flex">
                            <select class="form-control text-end" name="ate_partir_dia" required
                                style="max-width: 50%; margin-right: 10px;">
                                <option value="" disabled
                                    {{ old('ate_partir_dia', $ppp->ate_partir_dia ?? '') == '' ? 'selected' : '' }}>
                                    Selecione</option>
                                @foreach (['ate' => 'Até:', 'a_partir' => 'A partir de:', 'No_dia:' => 'No dia:'] as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('ate_partir_dia', $ppp->ate_partir_dia ?? '') == $val ? 'selected' : '' }}>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="date" name="data_ideal_aquisicao" class="form-control" style="max-width: 50%;"
                                required
                                value="{{ old('data_ideal_aquisicao', !empty($ppp->data_ideal_aquisicao) ? Carbon::parse($ppp->data_ideal_aquisicao)->format('Y-m-d') : '') }}" />
                        </div>
                    </div>
                </div>

                {{-- 6ª Linha --}}
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Será renovação de Contrato?</label>
                        <select name="renov_contrato" class="form-control" required>
                            <option value="" disabled
                                {{ old('renov_contrato', $ppp->renov_contrato ?? '') == '' ? 'selected' : '' }}>Selecione
                            </option>
                            <option value="Sim"
                                {{ old('renov_contrato', $ppp->renov_contrato ?? '') == 'Sim' ? 'selected' : '' }}>Sim
                            </option>
                            <option value="Não"
                                {{ old('renov_contrato', $ppp->renov_contrato ?? '') == 'Não' ? 'selected' : '' }}>Não
                            </option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Previsão</label>
                        <input type="date" name="previsao" class="form-control"
                            value="{{ old('previsao', $ppp->previsao ?? '') }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Número do Contrato</label>
                        <input type="text" name="num_contrato" class="form-control"
                            value="{{ old('num_contrato', $ppp->num_contrato ?? '') }}" placeholder="0001/2023"
                            autocomplete="off">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Valor do Contrato (atualizado)</label>
                        <input type="text" name="valor_contrato_atualizado"
                            class="form-control valor_contrato_atualizado"
                            value="{{ old('valor_contrato_atualizado', $ppp->valor_contrato_atualizado ?? '') }}"
                            placeholder="R$ 0,00" autocomplete="off">
                    </div>
                </div>

                {{-- Botão --}}
                <div class="card-footer text-center">
                    <a href="{{ route('ppp.index') }}" class="btn btn-secondary btn-lg px-5 py-2 me-2">Cancelar</a>
                    <button type="submit" class="btn btn-success btn-lg px-5 py-2">
                        {{ $edicao ? 'Atualizar' : 'Salvar' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('js')
    <script src="{{ asset('vendor/jquery/jquery.maskMoney.js') }}"></script>
    <script>
        $(function() {
            $('.estimativa_valor, .valor_contrato_atualizado').maskMoney({
                prefix: 'R$ ',
                allowNegative: false,
                thousands: '.',
                decimal: ',',
                affixesStay: true,
                allowZero: true
            });

            $('.estimativa_valor, .valor_contrato_atualizado').maskMoney('mask');

            $('select[name="vinculacao_item"]').on('change', function() {
                const isSim = $(this).val() === 'Sim';
                const input = $('input[name="justificativa_vinculacao"]');
                input.prop('required', isSim).prop('disabled', !isSim);
                input.css('background-color', isSim ? '' : '#e9ecef');
                if (!isSim) input.val('');
            });

            $('select[name="renov_contrato"]').on('change', function() {
                const isSim = $(this).val() === 'Sim';
                const campos = [
                    $('input[name="previsao"]'),
                    $('input[name="num_contrato"]'),
                    $('input[name="valor_contrato_atualizado"]')
                ];
                campos.forEach(input => {
                    input.prop('required', isSim).prop('disabled', !isSim);
                    input.css('background-color', isSim ? '' : '#e9ecef');
                    if (!isSim) input.val('');
                });
            });

            $('select[name="vinculacao_item"]').trigger('change');
            $('select[name="renov_contrato"]').trigger('change');
        });
    </script>
@endsection
