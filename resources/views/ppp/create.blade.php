@extends('adminlte::page')

@section('title', 'Criar novo PPP')

@section('content_header')
    <div class="text-center mb-0">
        <h1 class="fw-bold" style="font-size: 3rem;">PPP</h1>
        <small class="text-muted" style="font-size: 1rem;">Proposta para PCA</small>
    </div>
@endsection
@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Formulário a ser aprovado pelo coordenador da área</h3>
        </div>
        <form method="POST" action="{{ route('ppp.store') }}">
            @csrf
            <div class="card-body">
                {{-- 1ª Linha Área Solicitante --}}
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Área Solicitante</label>
                        <input type="text" class="form-control" name="area_solicitante"
                            value="{{ auth()->user()->area_solicitante_formatada }}" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Área Responsável</label>
                        <input type="text" class="form-control" name="area_responsavel"
                            value="{{ auth()->user()->area_responsavel_formatada }}" readonly>
                    </div>
                </div>
                <div class="form-row">
                    {{-- 2ª Linha: Categoria --}}
                    <div class="form-group col-md-4">
                        <label>Categoria</label>
                        <select class="form-control" id="categoria" name="categoria" required>
                            <option value="" disabled selected>Selecione</option>
                            <option value="Aquisição de bens">Aquisição de bens</option>
                            <option value="Contratação de Serviço">Contratação de Serviço</option>
                            <option value="Obras">Obras</option>
                            <option value="T.I.">T.I.</option>
                        </select>
                    </div>
                    <div class="form-group col-md-5">
                        <label>Nome do Item</label>
                        <input type="text" name="nome_item" class="form-control" required
                            placeholder="Ex: Aluguel de impressoras ou Consultoria para suporte em TI" autocomplete="off">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Quantidade</label>
                        <input type="text" name="quantidade" class="form-control" required
                            placeholder="Ex: 2 unidades ou 1 visita por ano" autocomplete="off">
                    </div>

                    {{-- 3ª Linha: Descrição do item --}}
                    <div class="form-group col-md-6">
                        <label>Descrição do objeto</label>
                        <textarea name="descricao" class="form-control" rows="4" required
                            placeholder="Ex: Impressora multifuncional Brother MFC-L8900CDW, laser colorida, 31 páginas por minuto, wireless,frente e verso automático.&#10;ou&#10;Consultoria para padronização de processos (remoto e in loco)"></textarea>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Justificativa para aquisição do item</label>
                        <textarea name="justificativa_pedido" class="form-control" rows="4" required
                            placeholder="Ex: Devido ao aumento de demanda após a contratação de novos funcionários."></textarea>
                    </div>

                    {{-- 4ª Linha: Estimativa de valor --}}
                    <div class="form-group col-md-2">
                        <label>Estimativa de Valor</label>
                        <input type="text" name="estimativa_valor" class="form-control estimativa_valor" required
                            placeholder="R$ 0,00" autocomplete="off">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Origem do Recurso</label>
                        <select class="form-control" id="origem_recurso" name="origem_recurso" required>
                            <option value="" disabled selected>Selecione</option>
                            <option value="Paranacidade">Paranacidade</option>
                            <option value="BID/FDU">BID/FDU</option>
                            <option value="FDU">FDU</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Fonte justificativa do valor</label>
                        <input type="text" name="justificativa_valor" class="form-control"
                            placeholder="Cotação realizada dia 01/01/2025 no portal do governo" autocomplete="off" required
                            autocomplete="off">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Prioridade</label>
                        <select class="form-control" id="grau_prioridade" name="grau_prioridade" required>
                            <option value="" disabled selected>Selecione</option>
                            <option value="Alta">Alta</option>
                            <option value="Média">Média</option>
                            <option value="Baixa">Baixa</option>
                        </select>
                    </div>

                    {{-- 5ª Linha: Vinculação de item --}}
                    <div class="form-group col-md-2">
                        <label>Vinculação/Dependência</label>
                        <select name="vinculacao_item" class="form-control" required>
                            <option value="" disabled selected>Selecione</option>
                            <option value="Sim">Sim</option>
                            <option value="Não">Não</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Justificativa da Vinculação/Dependência com outro item</label>
                        <input type="text" name="justificativa_vinculacao" class="form-control"
                            placeholder="Identificar a qual item o pedido está vinculado e justificar" autocomplete="off">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Data Ideal para Contratação</label>
                        <div class="d-flex">
                            <select class="form-control text-end" id="ate_partir_dia" name="ate_partir_dia" required
                                style="max-width: 50%; margin-right: 10px;">
                                <option value="" disabled selected>Selecione</option>
                                <option value="ate">Até:</option>
                                <option value="a_partir">A partir de:</option>
                                <option value="No_dia:">No dia:</option>
                            </select>
                            <input type="date" name="data_ideal_aquisicao" class="form-control" required
                                style="max-width: 50%;">
                        </div>
                    </div>

                    {{-- 6ª Linha: Contrato --}}
                    <div class="form-group col-md-3">
                        <label>Será renovação de Contrato?</label>
                        <select name="renov_contrato" class="form-control" required>
                            <option value="" disabled selected>Selecione</option>
                            <option value="Sim">Sim</option>
                            <option value="Não">Não</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Previsão</label>
                        <input type="date" name="previsao" class="form-control">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Número do Contrato</label>
                        <input type="text" name="num_contrato" class="form-control" placeholder="0001/2023"
                            autocomplete="off">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Valor do Contrato (atualizado)</label>
                        <input type="text" name="valor_contrato_atualizado"
                            class="form-control valor_contrato_atualizado" placeholder="R$ 0,00" autocomplete="off">
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button type="submit" class="btn btn-success btn-lg px-5 py-2">Salvar</button>
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
            $('.estimativa_valor, .valor_contrato_atualizado').maskMoney('mask', '0');

            // Justificativa Vinculação (se vinculado)
            $('select[name="vinculacao_item"]').on('change', function() {
                const isSim = $(this).val() === '1'; // Corrigido
                $('input[name="justificativa_vinculacao"]').prop('required', isSim);
            });

            // Renovação de contrato
            $('select[name="renov_contrato"]').on('change', function() {
                const isSim = $(this).val() === '1'; // Corrigido

                $('input[name="previsao"]').prop('required', isSim).prop('disabled', !isSim);
                $('input[name="num_contrato"]').prop('required', isSim).prop('disabled', !isSim);
                $('input[name="valor_contrato_atualizado"]').prop('required', isSim).prop('disabled', !
                    isSim);

                if (!isSim) {
                    $('input[name="previsao"]').val('');
                    $('input[name="num_contrato"]').val('');
                    $('input[name="valor_contrato_atualizado"]').val('');
                }
            });

            // Garante que o estado inicial esteja correto
            $('select[name="vinculacao_item"]').trigger('change');
            $('select[name="renov_contrato"]').trigger('change');
        });
    </script>
@endsection
