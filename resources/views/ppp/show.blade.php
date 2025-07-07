@extends('layouts.adminlte-custom')

@section('title', 'Visualizar PPP')

@section('content_header')
    <h1>Visualizar PPP</h1>
@stop

@section('content')
<!-- Debug Info (remover depois) -->
@if(config('app.debug'))
    <div class="alert alert-info">
        <strong>Debug:</strong><br>
        Usuário atual: {{ auth()->user()->name }}<br>
        Roles: {{ auth()->user()->roles->pluck('name')->join(', ') }}<br>
        Status do PPP: {{ $ppp->status_fluxo }}<br>
        Gestor atual ID: {{ $ppp->gestor_atual_id }}<br>
        Usuário atual ID: {{ auth()->id() }}<br>
        Condição hasAnyRole: {{ auth()->user()->hasAnyRole(['admin', 'daf', 'gestor']) ? 'true' : 'false' }}<br>
        Condição status: {{ $ppp->status_fluxo === 'aguardando_aprovacao' ? 'true' : 'false' }}
    </div>
@endif

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Card Principal -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-file-contract mr-2"></i>{{ $ppp->nome_item }}
                <small class="ml-2">#{{ $ppp->id }}</small>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Primeira Coluna -->
                <div class="col-md-2">

                    
                    <div class="form-group">
                        <label class="font-weight-bold">Categoria:</label>
                        <p class="form-control-plaintext">{{ $ppp->categoria ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Nome do Item:</label>
                        <p class="form-control-plaintext">{{ $ppp->nome_item }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Quantidade:</label>
                        <p class="form-control-plaintext">{{ $ppp->quantidade ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Prioridade:</label>
                        <p class="form-control-plaintext">
                            @if($ppp->grau_prioridade)
                                <span class="badge 
                                    @if($ppp->grau_prioridade === 'Alta') badge-danger
                                    @elseif($ppp->grau_prioridade === 'Média') badge-warning
                                    @else badge-secondary
                                    @endif">
                                    {{ $ppp->grau_prioridade }}
                                </span>
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
                
                <!-- Segunda Coluna -->
                <div class="col-md-5">
                    <div class="form-group">
                        <label class="font-weight-bold">Estimativa de Valor:</label>
                        <p class="form-control-plaintext text-success font-weight-bold">
                            R$ {{ number_format($ppp->estimativa_valor ?? 0, 2, ',', '.') }}
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Origem do Recurso:</label>
                        <p class="form-control-plaintext">{{ $ppp->origem_recurso ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Fonte justificativa do valor:</label>
                        <p class="form-control-plaintext">{{ $ppp->justificativa_valor ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Data Ideal para Contratação:</label>
                        <p class="form-control-plaintext">
                            @if($ppp->ate_partir_dia && $ppp->data_ideal_aquisicao)
                                {{ ucfirst(str_replace(['ate', 'a_partir', 'No_dia:'], ['Até', 'A partir de', 'No dia'], $ppp->ate_partir_dia)) }} 
                                {{ \Carbon\Carbon::parse($ppp->data_ideal_aquisicao)->format('d/m/Y') }}
                            @elseif($ppp->data_ideal_aquisicao)
                                {{ \Carbon\Carbon::parse($ppp->data_ideal_aquisicao)->format('d/m/Y') }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Será renovação de Contrato?:</label>
                        <p class="form-control-plaintext">{{ $ppp->renov_contrato ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Status:</label>
                        <p class="form-control-plaintext">
                            <span class="badge badge-info">
                                @if($ppp->statusDinamicos->where('ativo', true)->first())
                                    <i class="fas fa-info-circle mr-1"></i>{{ $ppp->statusDinamicos->where('ativo', true)->first()->status_formatado }}
                                @else
                                    <i class="fas fa-clock mr-1"></i>Rascunho
                                @endif
                            </span>
                        </p>
                    </div>
                </div>
                
                <!-- Terceira Coluna -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Descrição do objeto:</label>
                        <p class="form-control-plaintext">{{ $ppp->descricao ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Justificativa para aquisição:</label>
                        <p class="form-control-plaintext">{{ $ppp->justificativa_pedido ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Vinculação/Dependência:</label>
                        <p class="form-control-plaintext">{{ $ppp->vinculacao_item ?? 'N/A' }}</p>
                    </div>
                    
                    @if($ppp->vinculacao_item === 'Sim')
                    <div class="form-group">
                        <label class="font-weight-bold">Justificativa da Vinculação:</label>
                        <p class="form-control-plaintext">{{ $ppp->justificativa_vinculacao ?? 'N/A' }}</p>
                    </div>
                    @endif
                    
                    @if($ppp->renov_contrato === 'Sim')
                    <div class="form-group">
                        <label class="font-weight-bold">Previsão:</label>
                        <p class="form-control-plaintext">
                            {{ $ppp->previsao ? \Carbon\Carbon::parse($ppp->previsao)->format('d/m/Y') : 'N/A' }}
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Número do Contrato:</label>
                        <p class="form-control-plaintext">{{ $ppp->num_contrato ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Valor do Contrato (atualizado):</label>
                        <p class="form-control-plaintext text-info font-weight-bold">
                            @if($ppp->valor_contrato_atualizado)
                                R$ {{ number_format($ppp->valor_contrato_atualizado, 2, ',', '.') }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    @endif
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Criado em:</label>
                        <p class="form-control-plaintext">
                            {{ $ppp->created_at ? $ppp->created_at->format('d/m/Y H:i') : 'N/A' }}
                        </p>
                    </div>
                </div>
                
                <!-- Quarta coluna: Botões de Ação -->
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ações</h3>
                        </div>
                        <div class="card-body">
                            <div class="btn-group-vertical d-grid gap-2">
                                <!-- Botão Histórico -->
                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#historicoModal">
                                    <i class="fas fa-history"></i> Histórico
                                </button>
                                
                                <!-- Botão Editar -->
                                <a href="{{ route('ppp.edit', $ppp->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                
                                <!-- Botão Aprovar - Visível apenas para gestores -->
                                @if(auth()->user()->hasAnyRole(['admin', 'daf', 'gestor']) && $ppp->status_fluxo === 'aguardando_aprovacao' && $ppp->gestor_atual_id === auth()->id())
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#aprovarModal">
                                        <i class="fas fa-check"></i> Aprovar
                                    </button>
                                @endif
                                
                                <!-- Botão Retornar -->
                                <a href="{{ route('ppp.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Retornar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($ppp->observacoes)
            <div class="row mt-3">
                <div class="col-12">
                    <div class="form-group">
                        <label class="font-weight-bold">Observações:</label>
                        <p class="form-control-plaintext">{{ $ppp->observacoes }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Histórico -->
<div class="modal fade" id="historicoModal" tabindex="-1" role="dialog" aria-labelledby="historicoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="historicoModalLabel">
                    <i class="fas fa-history mr-2"></i>Histórico do PPP: {{ $ppp->nome_item }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if($historicos && $historicos->count() > 0)
                    <div class="timeline">
                        @foreach($historicos as $historico)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">
                                        @if($historico->status_anterior)
                                            Status alterado
                                        @else
                                            PPP Criado
                                        @endif
                                    </h6>
                                    <p class="timeline-text">
                                        @if($historico->status_anterior)
                                            Status alterado para: <strong>{{ $historico->statusAtual->nome ?? 'Status atual' }}</strong>
                                        @else
                                            PPP foi criado com sucesso.
                                        @endif
                                    </p>
                                    @if($historico->justificativa)
                                        <p class="timeline-text"><strong>Justificativa:</strong> {{ $historico->justificativa }}</p>
                                    @endif
                                    <small class="text-muted">
                                        {{ $historico->created_at ? $historico->created_at->format('d/m/Y H:i') : 'Data não disponível' }}
                                        @if($historico->usuario)
                                            - por {{ $historico->usuario->name }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Histórico detalhado será implementado em breve.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Fechar
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Aprovar -->
<div class="modal fade" id="aprovarModal" tabindex="-1" role="dialog" aria-labelledby="aprovarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="aprovarModalLabel">
                    <i class="fas fa-check mr-2"></i>Aprovar PPP
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('ppp.aprovar', $ppp->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Tem certeza que deseja aprovar este PPP?</p>
                    <p><strong>PPP:</strong> {{ $ppp->nome_item }}</p>
                    
                    <div class="form-group">
                        <label for="comentario_aprovacao">Comentário (opcional):</label>
                        <textarea name="comentario" id="comentario_aprovacao" class="form-control" rows="3" placeholder="Adicione um comentário sobre a aprovação..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i>Confirmar Aprovação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-left: 2px solid #e9ecef;
    }
    
    .timeline-item:last-child {
        border-left: none;
    }
    
    .timeline-marker {
        position: absolute;
        left: -6px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
    }
    
    .timeline-content {
        margin-left: 20px;
    }
    
    .timeline-title {
        margin-bottom: 5px;
        font-weight: 600;
        color: #495057;
    }
    
    .timeline-text {
        margin-bottom: 5px;
        color: #6c757d;
    }
    
    .btn-actions-container {
        min-height: 200px;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
    }
</style>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
@endsection
