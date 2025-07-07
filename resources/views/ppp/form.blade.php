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
                <i class="fas fa-file-contract text-primary me-3" style="font-size: 2.5rem;"></i>
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
        <!-- Seção 1: Informações do Item -->
        <div class="col-12 mb-4">
            <div class="card card-outline card-primary">
                <div class="card-header bg-primary">
                    <h3 class="card-title text-white">
                        <i class="fas fa-box me-2"></i>
                        Informações do Item/Serviço
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tag text-primary me-1"></i>
                                Nome do Item <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nome_item" class="form-control form-control-lg @error('nome_item') is-invalid @enderror" required
                                value="{{ old('nome_item', $ppp->nome_item ?? '') }}"
                                placeholder="Ex: Aluguel de impressoras ou Consultoria para suporte em TI">
                            @error('nome_item')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-cubes text-primary me-1"></i>
                                Quantidade <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="quantidade" class="form-control form-control-lg @error('quantidade') is-invalid @enderror" required
                                value="{{ old('quantidade', $ppp->quantidade ?? '') }}"
                                placeholder="Ex: 2 unidades ou 1 visita/ano">
                            @error('quantidade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-exclamation-triangle text-primary me-1"></i>
                                Grau de prioridade <span class="text-danger">*</span>
                            </label>
                            <select class="form-control form-control-lg" name="grau_prioridade" required>
                                <option value="" disabled {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                                <option value="Alta" {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == 'Alta' ? 'selected' : '' }}>🔴 Alta</option>
                                <option value="Média" {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == 'Média' ? 'selected' : '' }}>🟡 Média</option>
                                <option value="Baixa" {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == 'Baixa' ? 'selected' : '' }}>🟢 Baixa</option>
                            </select>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-lg-8 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-file-alt text-primary me-1"></i>
                                Descrição sucinta do objeto <span class="text-danger">*</span>
                            </label>
                            <textarea name="descricao" class="form-control @error('descricao') is-invalid @enderror" rows="4" maxlength="500" required 
                                placeholder="Descreva detalhadamente o objeto da contratação">{{ old('descricao', $ppp->descricao ?? '') }}</textarea>
                            @error('descricao')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-cog text-primary me-1"></i>
                                Natureza do Objeto <span class="text-danger">*</span>
                            </label>
                            <select class="form-control form-control-lg" name="natureza_objeto" required>
                                <option value="" disabled {{ old('natureza_objeto', $ppp->natureza_objeto ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                                @foreach (['Serviço não continuado', 'Serviço continuado', 'Material de consumo', 'Bem permanente/equipamento'] as $natureza)
                                    <option value="{{ $natureza }}" {{ old('natureza_objeto', $ppp->natureza_objeto ?? '') == $natureza ? 'selected' : '' }}>{{ $natureza }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <label class="form-label fw-bold">
                                <i class="fas fa-clipboard-list text-primary me-1"></i>
                                Justificativa da necessidade <span class="text-danger">*</span>
                            </label>
                            <textarea name="justificativa_pedido" class="form-control @error('justificativa_pedido') is-invalid @enderror" rows="4" maxlength="1000" required 
                                placeholder="Justifique detalhadamente a necessidade desta contratação">{{ old('justificativa_pedido', $ppp->justificativa_pedido ?? '') }}</textarea>
                            @error('justificativa_pedido')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-list text-primary me-1"></i>
                                Categoria <span class="text-danger">*</span>
                            </label>
                            <select class="form-control form-control-lg" name="categoria" required>
                                <option value="" disabled {{ old('categoria', $ppp->categoria ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                                @foreach (['Aquisição de bens', 'Contratação de Serviço', 'Obras', 'T.I.'] as $cat)
                                    <option value="{{ $cat }}" {{ old('categoria', $ppp->categoria ?? '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção 2: Informações Financeiras -->
        <div class="col-12 mb-4">
            <div class="card card-outline card-success">
                <div class="card-header bg-success">
                    <h3 class="card-title text-white">
                        <i class="fas fa-dollar-sign me-2"></i>
                        Informações Financeiras
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-money-bill-wave text-success me-1"></i>
                                Valor total estimado (exercício) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="estimativa_valor" class="form-control form-control-lg estimativa_valor money-field" required
                                value="{{ old('estimativa_valor', $ppp->estimativa_valor ?? '') }}" 
                                placeholder="R$ 0,00" autocomplete="off">
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-university text-success me-1"></i>
                                Origem do recurso <span class="text-danger">*</span>
                            </label>
                            <select class="form-control form-control-lg" name="origem_recurso" required>
                                <option value="" disabled {{ old('origem_recurso', $ppp->origem_recurso ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                                @foreach (['PRC', 'FDU', 'BID/FDU'] as $origem)
                                    <option value="{{ $origem }}" {{ old('origem_recurso', $ppp->origem_recurso ?? '') == $origem ? 'selected' : '' }}>{{ $origem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-plus text-success me-1"></i>
                                Valor se +1 exercício
                            </label>
                            <input type="text" name="valor_contrato_atualizado" class="form-control form-control-lg valor_contrato_atualizado money-field"
                                value="{{ old('valor_contrato_atualizado', $ppp->valor_contrato_atualizado ?? '') }}"
                                placeholder="R$ 0,00" autocomplete="off">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calculator text-success me-1"></i>
                                Justificativa do valor estimado <span class="text-danger">*</span>
                            </label>
                            <textarea name="justificativa_valor" class="form-control @error('justificativa_valor') is-invalid @enderror" rows="3" maxlength="800" required
                                placeholder="Ex: Cotação realizada em 01/01/2025 no portal gov. Índice de aumento x% conforme indicador y">{{ old('justificativa_valor', $ppp->justificativa_valor ?? '') }}</textarea>
                            @error('justificativa_valor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção 3: Cronograma -->
        <div class="col-12 mb-4">
            <div class="card card-outline card-secondary">
                <div class="card-header bg-secondary">
                    <h3 class="card-title text-white">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Cronograma de Aquisição
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-clock text-secondary me-1"></i>
                                A partir de quando pode ser adquirido <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="ate_partir_dia" class="form-control form-control-lg" required
                                value="{{ old('ate_partir_dia', $ppp->ate_partir_dia ?? '') }}"
                                placeholder="Ex: A partir de março/2025">
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-check text-secondary me-1"></i>
                                Data ideal para aquisição <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="data_ideal_aquisicao" class="form-control form-control-lg" required
                                value="{{ old('data_ideal_aquisicao', $ppp->data_ideal_aquisicao ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção 4: Contratos e Vinculação -->
        <div class="col-lg-6 mb-4">
            <div class="card card-outline card-warning h-100">
                <div class="card-header bg-warning">
                    <h3 class="card-title text-dark">
                        <i class="fas fa-file-contract me-2"></i>
                        Contrato Vigente
                    </h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-question-circle text-warning me-1"></i>
                            Objeto tem contrato vigente? <span class="text-danger">*</span>
                        </label>
                        <select name="tem_contrato_vigente" id="tem_contrato_vigente" class="form-control form-control-lg" required>
                            <option value="" disabled {{ old('tem_contrato_vigente', $ppp->tem_contrato_vigente ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                            <option value="Sim" {{ old('tem_contrato_vigente', $ppp->tem_contrato_vigente ?? '') == 'Sim' ? 'selected' : '' }}>✅ Sim</option>
                            <option value="Não" {{ old('tem_contrato_vigente', $ppp->tem_contrato_vigente ?? '') == 'Não' ? 'selected' : '' }}>❌ Não</option>
                        </select>
                    </div>

                    <div id="campos_contrato" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-hashtag text-warning me-1"></i>
                                Número/Ano do contrato
                            </label>
                            <input type="text" name="num_contrato" id="num_contrato" class="form-control contract-number"
                                value="{{ old('num_contrato', $ppp->num_contrato ?? '') }}" 
                                placeholder="Ex: 0001/2023" autocomplete="off">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-times text-warning me-1"></i>
                                Mês da vigência final prevista
                            </label>
                            <input type="month" name="mes_vigencia_final" class="form-control"
                                value="{{ old('mes_vigencia_final', $ppp->mes_vigencia_final ?? '') }}">
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Prorrogável?</label>
                                <select name="contrato_prorrogavel" id="contrato_prorrogavel" class="form-control">
                                    <option value="" disabled {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                                    <option value="Sim" {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                                    <option value="Não" {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Pretensão de prorrogação?</label>
                                <select name="renov_contrato" id="renov_contrato" class="form-control">
                                    <option value="" disabled {{ old('renov_contrato', $ppp->renov_contrato ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                                    <option value="Sim" {{ old('renov_contrato', $ppp->renov_contrato ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                                    <option value="Não" {{ old('renov_contrato', $ppp->renov_contrato ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção 5: Vinculação -->
        <div class="col-lg-6 mb-4">
            <div class="card card-outline card-info h-100">
                <div class="card-header bg-info">
                    <h3 class="card-title text-white">
                        <i class="fas fa-link me-2"></i>
                        Vinculação/Dependência
                    </h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-question-circle text-info me-1"></i>
                            Possui vinculação/dependência? <span class="text-danger">*</span>
                        </label>
                        <select name="vinculacao_item" id="vinculacao_item" class="form-control form-control-lg" required>
                            <option value="" disabled {{ old('vinculacao_item', $ppp->vinculacao_item ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                            <option value="Sim" {{ old('vinculacao_item', $ppp->vinculacao_item ?? '') == 'Sim' ? 'selected' : '' }}>✅ Sim</option>
                            <option value="Não" {{ old('vinculacao_item', $ppp->vinculacao_item ?? '') == 'Não' ? 'selected' : '' }}>❌ Não</option>
                        </select>
                    </div>

                    <div id="campo_justificativa_vinculacao" style="display: none;">
                        <label class="form-label fw-bold">
                            <i class="fas fa-edit text-info me-1"></i>
                            Justificativa da vinculação
                        </label>
                        <textarea name="justificativa_vinculacao" id="justificativa_vinculacao" class="form-control" rows="4" maxlength="600"
                            placeholder="Identifique a qual item o pedido está vinculado e justifique a dependência">{{ old('justificativa_vinculacao', $ppp->justificativa_vinculacao ?? '') }}</textarea>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>
                            <strong>Vinculação:</strong> Quando este item depende de outro para funcionar adequadamente.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-4">
                    <a href="{{ route('ppp.index') }}" class="btn btn-secondary btn-lg me-3">
                        <i class="fas fa-times me-2"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save me-2"></i>
                        {{ $edicao ? 'Atualizar PPP' : 'Salvar PPP' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('css')
<style>
    .card-outline {
        border: 2px solid;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.15s ease-in-out;
    }
    
    .card-outline:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .form-label.fw-bold {
        font-size: 0.95rem;
        margin-bottom: 0.5rem;
    }
    
    .form-control-lg {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        transition: all 0.15s ease-in-out;
    }
    
    .form-control-lg:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .text-danger {
        font-weight: bold;
    }
    
    .alert-info {
        border-left: 4px solid #17a2b8;
    }
    
    /* Estilos para campos com máscaras */
    .money-field {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        text-align: right;
        background: linear-gradient(45deg, #f8f9fa 0%, #ffffff 100%);
    }
    
    .contract-number {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        text-align: center;
        letter-spacing: 1px;
    }
    
    /* Contadores de caracteres */
    .char-counter {
        font-size: 0.8rem;
        font-weight: 500;
        transition: color 0.3s ease;
    }
    
    /* Validação visual */
    .is-valid {
        border-color: #28a745 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73l.4-.4 1.4-1.4.7-.4.4.7-2.1 2.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem);
    }
    
    .is-invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke='%23dc3545' d='m5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem);
    }
    
    /* Animações suaves */
    .form-control, .form-select {
        transition: all 0.3s ease;
    }
    
    .form-control:hover:not(:focus) {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.1rem rgba(0, 123, 255, 0.1);
    }
    
    /* Tooltip para campos obrigatórios */
    .form-label[data-required="true"]::after {
        content: "";
        margin-left: 0.25rem;
        color: #dc3545;
        font-weight: bold;
    }
    
    /* Placeholder personalizado */
    .form-control::placeholder {
        color: #6c757d;
        opacity: 0.8;
        font-style: italic;
    }
    
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        
        .btn-lg {
            padding: 0.5rem 1rem;
            font-size: 1rem;
        }
        
        .money-field {
            font-size: 0.9rem;
        }
        
        .char-counter {
            font-size: 0.7rem;
        }
    }
</style>
@endsection

@section('js')
    <script src="{{ asset('vendor/jquery/jquery.maskMoney.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(function() {
            // Máscaras de dinheiro - usando maskMoney para melhor formatação
            $('.estimativa_valor, .valor_contrato_atualizado').maskMoney({
                prefix: 'R$ ',
                allowNegative: false,
                thousands: '.',
                decimal: ',',
                affixesStay: true,
                allowZero: true,
                precision: 2
            });

            // Aplicar máscaras de dinheiro
            $('.estimativa_valor, .valor_contrato_atualizado').maskMoney('mask');

            // Máscara para número de contrato (formato: 0000/0000)
            $('#num_contrato').mask('0000/0000', {
                placeholder: '____/____',
                translation: {
                    '0': {pattern: /[0-9]/}
                }
            });

            // Máscara para quantidade (permitir números, vírgulas, pontos e texto)
            $('input[name="quantidade"]').on('input', function() {
                // Permitir números, vírgulas, pontos, espaços e letras
                let value = $(this).val();
                // Remove caracteres especiais indesejados, mantendo apenas letras, números, espaços, vírgulas e pontos
                value = value.replace(/[^a-zA-ZÀ-ÿ0-9\s,.-]/g, '');
                $(this).val(value);
            });

            // Máscara e formatação automática para campos de texto longos
            $('input[name="nome_item"]').on('input', function() {
                let value = $(this).val();
                // Capitalizar primeira letra de cada palavra
                value = value.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
                $(this).val(value);
            });



            // Formatação para campo "a partir de quando"
            $('input[name="ate_partir_dia"]').on('input', function() {
                let value = $(this).val();
                // Capitalizar primeira letra de cada palavra
                value = value.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
                $(this).val(value);
            });

            // Formatação automática para textareas - limitar caracteres e contar
            $('textarea').each(function() {
                const textarea = $(this);
                const maxLength = textarea.attr('maxlength') || 1000;
                
                // Criar contador se não existir
                if (!textarea.next('.char-counter').length) {
                    textarea.after(`<small class="char-counter text-muted float-end mt-1">0/${maxLength} caracteres</small>`);
                }
                
                textarea.on('input', function() {
                    const currentLength = $(this).val().length;
                    const counter = $(this).next('.char-counter');
                    counter.text(`${currentLength}/${maxLength} caracteres`);
                    
                    // Mudar cor quando próximo do limite
                    if (currentLength > maxLength * 0.9) {
                        counter.removeClass('text-muted').addClass('text-warning');
                    } else if (currentLength === maxLength) {
                        counter.removeClass('text-warning').addClass('text-danger');
                    } else {
                        counter.removeClass('text-warning text-danger').addClass('text-muted');
                    }
                });
                
                // Trigger inicial
                textarea.trigger('input');
            });

            // Validação em tempo real para campos obrigatórios
            $('input[required], select[required], textarea[required]').on('blur', function() {
                const field = $(this);
                const value = field.val().trim();
                
                if (value === '' || value === null) {
                    field.addClass('is-invalid');
                    if (!field.next('.invalid-feedback').length) {
                        field.after('<div class="invalid-feedback">Este campo é obrigatório.</div>');
                    }
                } else {
                    field.removeClass('is-invalid').next('.invalid-feedback').remove();
                    field.addClass('is-valid');
                }
            });

            // Limpar validação ao focar no campo
            $('input, select, textarea').on('focus', function() {
                $(this).removeClass('is-invalid is-valid').next('.invalid-feedback').remove();
            });

            // Formatação automática para valores monetários durante digitação
            $('.estimativa_valor, .valor_contrato_atualizado').on('keyup', function() {
                $(this).maskMoney('mask');
            });

            // Lógica para campos condicionais - Vinculação
            $('#vinculacao_item').on('change', function() {
                const isSim = $(this).val() === 'Sim';
                const campoJustificativa = $('#campo_justificativa_vinculacao');
                const textarea = $('#justificativa_vinculacao');
                
                if (isSim) {
                    campoJustificativa.slideDown(300);
                    textarea.prop('required', true);
                } else {
                    campoJustificativa.slideUp(300);
                    textarea.prop('required', false).val('').removeClass('is-invalid is-valid').next('.invalid-feedback').remove();
                }
            });

            // Lógica para campos condicionais - Contrato Vigente
            $('#tem_contrato_vigente').on('change', function() {
                const temContrato = $(this).val() === 'Sim';
                const camposContrato = $('#campos_contrato');
                const inputs = camposContrato.find('input, select');
                
                if (temContrato) {
                    camposContrato.slideDown(300);
                    inputs.prop('disabled', false);
                    // Tornar apenas alguns campos obrigatórios quando tem contrato
                    $('#num_contrato, #contrato_prorrogavel, #renov_contrato').prop('required', true);
                } else {
                    camposContrato.slideUp(300);
                    inputs.prop('disabled', true).prop('required', false).val('')
                          .removeClass('is-invalid is-valid').next('.invalid-feedback').remove();
                }
            });

            // Trigger inicial para estado correto dos campos
            $('#vinculacao_item').trigger('change');
            $('#tem_contrato_vigente').trigger('change');

            // Animação suave para o submit
            $('form').on('submit', function(e) {
                const submitBtn = $(this).find('button[type="submit"]');
                
                // Debug: mostrar todos os dados antes de enviar
                console.log('Dados do formulário:', $(this).serialize());
                
                // Verificar campos obrigatórios manualmente
                let camposVazios = [];
                $('input[required], select[required], textarea[required]').each(function() {
                    if (!$(this).prop('disabled') && !$(this).val().trim()) {
                        camposVazios.push($(this).attr('name') || 'campo sem nome');
                        $(this).addClass('is-invalid');
                    }
                });
                
                if (camposVazios.length > 0) {
                    e.preventDefault();
                    console.log('Campos obrigatórios vazios:', camposVazios);
                    alert('Campos obrigatórios não preenchidos: ' + camposVazios.join(', '));
                    return false;
                }
                
                submitBtn.prop('disabled', true)
                       .html('<i class="fas fa-spinner fa-spin me-2"></i>Salvando...');
            });

            // Auto-save em localStorage (rascunho)
            const formId = 'ppp-form-draft';
            
            // Salvar rascunho a cada 30 segundos
            setInterval(function() {
                const formData = {};
                $('input, select, textarea').each(function() {
                    if ($(this).attr('name') && $(this).val()) {
                        formData[$(this).attr('name')] = $(this).val();
                    }
                });
                localStorage.setItem(formId, JSON.stringify(formData));
            }, 30000);

            // Recuperar rascunho ao carregar a página
           

            // Limpar rascunho após envio bem-sucedido
            $('form').on('submit', function() {
                localStorage.removeItem(formId);
            });
        });
    </script>
@endsection
