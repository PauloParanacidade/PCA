@extends('layouts.adminlte-custom')

@php
    use Carbon\Carbon;
@endphp

@php
    $edicao = isset($ppp);
@endphp

@section('title', $edicao ? 'Editar PPP' : 'Criar novo PPP')

@section('page_header')
    <div class="row align-items-center">
        <div class="col-md-4 text-left">
            <h4 class="mb-0">{{ $edicao ? 'Editar PPP' : 'Criar novo PPP' }}</h4>
        </div>
        <div class="col-md-8 text-center">
            <h1 class="fw-bold mb-0" style="font-size: 3rem;">PPP</h1>
            <small class="text-muted" style="font-size: 1rem;">Proposta para PCA</small>
        </div>
    </div>
@endsection

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                {{ $edicao ? 'Atualize as informações do PPP' : 'PPP a ser aprovado pelo superior imediato' }}
            </h3>
        </div>
        <form method="POST" action="{{ $edicao ? route('ppp.update', $ppp->id) : route('ppp.store') }}">
            @csrf
            @if ($edicao)
                @method('PUT')
            @endif

            <div class="card-body">
 {{-- 1ª Linha: Nome, Descrição sucinta e quantidade aninhados --}}
<div class="form-row">
    {{-- Coluna esquerda: Nome do Item + Quantidade --}}
    <div class="col-md-4">
        <div class="form-group mb-4">
            <label>Nome do Item</label>
            <input type="text" name="nome_item" class="form-control" required
                value="{{ old('nome_item', $ppp->nome_item ?? '') }}"
                placeholder="Ex: Aluguel de impressoras ou Consultoria para suporte em TI">
        </div>
        <div class="form-group">
            <label>Quantidade a ser contratada (quando couber)</label>
            <input type="text" name="quantidade" class="form-control" required
                value="{{ old('quantidade', $ppp->quantidade ?? '') }}"
                placeholder="Ex: 2 unidades ou 1 visita por ano">
        </div>
    </div>

    {{-- Coluna direita: Descrição sucinta --}}
    <div class="col-md-8">
        <div class="form-group">
            <label>Descrição sucinta do objeto</label>
            <textarea name="descricao" class="form-control" rows="5" required placeholder="Descreva o objeto">{{ old('descricao', $ppp->descricao ?? '') }}</textarea>
        </div>
    </div>
</div>

{{-- Linha: Categoria + Natureza do Objeto + campos relacionados --}}
<div class="form-row">
    {{-- Coluna esquerda: Categoria + Natureza + Contrato --}}
    <div class="col-md-4">
        <div class="form-row mb-2">
            <div class="form-group col-md-6">
                <label>Categoria</label>
                <select class="form-control" name="categoria" required>
                    <option value="" disabled {{ old('categoria', $ppp->categoria ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    @foreach (['Aquisição de bens', 'Contratação de Serviço', 'Obras', 'T.I.'] as $cat)
                        <option value="{{ $cat }}" {{ old('categoria', $ppp->categoria ?? '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-6">
                <label>Natureza do Objeto</label>
                <select class="form-control" name="natureza_objeto" required>
                    <option value="" disabled {{ old('natureza_objeto', $ppp->natureza_objeto ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    @foreach (['Serviço não continuado', 'Serviço continuado', 'Material de consumo', 'Bem permanente/equipamento'] as $natureza)
                        <option value="{{ $natureza }}" {{ old('natureza_objeto', $ppp->natureza_objeto ?? '') == $natureza ? 'selected' : '' }}>{{ $natureza }}</option>
                    @endforeach
                </select>
            </div>
        </div>


        {{-- Linha: Objeto tem contrato + Número/Ano --}}
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Objeto tem contrato vigente?</label>
                <select name="tem_contrato_vigente" id="tem_contrato_vigente" class="form-control" required>
                    <option value="" disabled {{ old('tem_contrato_vigente', $ppp->tem_contrato_vigente ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    <option value="Sim" {{ old('tem_contrato_vigente', $ppp->tem_contrato_vigente ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                    <option value="Não" {{ old('tem_contrato_vigente', $ppp->tem_contrato_vigente ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label>Número/Ano do contrato vigente</label>
                <input type="text" name="num_contrato" id="num_contrato" class="form-control"
                    value="{{ old('num_contrato', $ppp->num_contrato ?? '') }}" placeholder="0001/2023" autocomplete="off" disabled>
            </div>
            <div class="form-group">
            <label>Qual o mês da vigência final prevista?</label>
            <input type="month" name="mes_vigencia_final" class="form-control"
                value="{{ old('mes_vigencia_final', $ppp->mes_vigencia_final ?? '') }}">
        </div>
        <div class="form-group col-md-6">
                <label>Prorrogável?</label>
                <select name="contrato_prorrogavel" id="contrato_prorrogavel" class="form-control" disabled>
                    <option value="" disabled {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    <option value="Sim" {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                    <option value="Não" {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                </select>
            </div>
        </div>

        {{-- Linha: Prorrogável + Pretensão --}}
        <div class="form-row">
            
            <div class="form-group col-md-6">
                <label>Há pretensão de prorrogação?</label>
                <select name="renov_contrato" id="renov_contrato" class="form-control" disabled>
                    <option value="" disabled {{ old('renov_contrato', $ppp->renov_contrato ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    <option value="Sim" {{ old('renov_contrato', $ppp->renov_contrato ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                    <option value="Não" {{ old('renov_contrato', $ppp->renov_contrato ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                </select>
            </div>
            <div class="form-group col-md-6">
    <label>Grau de prioridade da contratação</label>
    <select class="form-control" name="grau_prioridade" required>
        <option value="" disabled {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == '' ? 'selected' : '' }}>Selecione</option>
        <option value="Alta" {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == 'Alta' ? 'selected' : '' }}>Alta</option>
        <option value="Média" {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == 'Média' ? 'selected' : '' }}>Média</option>
        <option value="Baixa" {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == 'Baixa' ? 'selected' : '' }}>Baixa</option>
    </select>
</div>


        </div>

        {{-- Mês da vigência final prevista --}}
        
    </div>

    {{-- Coluna direita: Justificativa --}}
    <div class="col-md-8">
        <div class="form-group">
            <label>Justificativa</label>
            <textarea name="justificativa_pedido" class="form-control" rows="5" required placeholder="Justifique a necessidade da contratação">{{ old('justificativa_pedido', $ppp->justificativa_pedido ?? '') }}</textarea>
        </div>
{{-- Linha: Valores estimados + Justificativa do valor --}}
<div class="form-row mt-2">
    {{-- Coluna esquerda: 3 campos em linha --}}
    <div class="col-md-9">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Valor total estimado (exercício)</label>
                <input type="number" name="estimativa_valor" class="form-control estimativa_valor" required
                    value="{{ old('estimativa_valor', $ppp->estimativa_valor ?? '') }}" placeholder="R$ 0,00" autocomplete="off">
            </div>
            <div class="form-group col-md-4">
                <label>Origem do recurso</label>
                <select class="form-control" name="origem_recurso" required>
                    <option value="" disabled {{ old('origem_recurso', $ppp->origem_recurso ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    @foreach (['PRC', 'FDU', 'BID/FDU'] as $origem)
                        <option value="{{ $origem }}" {{ old('origem_recurso', $ppp->origem_recurso ?? '') == $origem ? 'selected' : '' }}>{{ $origem }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label>Valor da contratação se +1 exercício</label>
                <input type="text" name="valor_contrato_atualizado" class="form-control valor_contrato_atualizado"
                    value="{{ old('valor_contrato_atualizado', $ppp->valor_contrato_atualizado ?? '') }}"
                    placeholder="R$ 0,00" autocomplete="off">
            </div>
            <div class="form-row">
            <div class="form-group col-md-4">
                <label>Vinculação/Dependência</label>
                <select name="vinculacao_item" class="form-control" required>
                    <option value="" disabled {{ old('vinculacao_item', $ppp->vinculacao_item ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    <option value="Sim" {{ old('vinculacao_item', $ppp->vinculacao_item ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                    <option value="Não" {{ old('vinculacao_item', $ppp->vinculacao_item ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                </select>
            </div>
            <div class="form-group col-md-8">
                <label>Justificativa da Vinculação/Dependência com outro item</label>
                <input type="text" name="justificativa_vinculacao" class="form-control"
                    value="{{ old('justificativa_vinculacao', $ppp->justificativa_vinculacao ?? '') }}"
                    placeholder="Identificar a qual item o pedido está vinculado e justificar" autocomplete="off">
            </div>
            
        </div>
        </div>
    </div>

    {{-- Coluna direita: Justificativa do valor como textarea --}}
    <div class="col-md-3">
        <div class="form-group">
            <label>Justificativa do valor</label>
            <textarea name="justificativa_valor" class="form-control" rows="5" required
                placeholder="Ex: Cotação realizada em 01/01/2025 no portal gov. Índice de aumento x% conforme indicador y">{{ old('justificativa_valor', $ppp->justificativa_valor ?? '') }}</textarea>
        </div>
    </div>
</div>

        {{-- Linha: Vinculação --}}
        
    </div>
</div>






                {{-- Botões --}}
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

            // Lógica para campos condicionais
            $('select[name="vinculacao_item"]').on('change', function() {
                const isSim = $(this).val() === 'Sim';
                const input = $('input[name="justificativa_vinculacao"]');
                input.prop('required', isSim).prop('disabled', !isSim);
                input.css('background-color', isSim ? '' : '#e9ecef');
                if (!isSim) input.val('');
            });

            $('select[name="tem_contrato_vigente"]').on('change', function() {
                const temContrato = $(this).val() === 'Sim';
                const campos = [
                    $('select[name="contrato_prorrogavel"]'),
                    $('input[name="num_contrato"]'),
                    $('select[name="renov_contrato"]'),
                    $('input[name="mes_vigencia_final"]')
                ];
                campos.forEach(input => {
                    input.prop('required', temContrato).prop('disabled', !temContrato);
                    input.css('background-color', temContrato ? '' : '#e9ecef');
                    if (!temContrato) input.val('');
                });
            });

            // Trigger inicial
            $('select[name="vinculacao_item"]').trigger('change');
            $('select[name="tem_contrato_vigente"]').trigger('change');
        });
    </script>
@endsection
