@extends('adminlte::page')

@section('title', 'Visualizar PPP')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-eye mr-2"></i>Visualizar PPP</h1>
        <div>
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#historicoModal" title="Histórico">
                <i class="fas fa-history mr-1"></i>Histórico
            </button>
            <a href="{{ route('ppp.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Retornar
            </a>
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
                <!-- Coluna Esquerda -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Área Solicitante:</label>
                        <p class="form-control-plaintext">{{ $ppp->area_solicitante ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Nome do Item:</label>
                        <p class="form-control-plaintext">{{ $ppp->nome_item }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Descrição:</label>
                        <p class="form-control-plaintext">{{ $ppp->descricao ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Justificativa:</label>
                        <p class="form-control-plaintext">{{ $ppp->justificativa ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <!-- Coluna Direita -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Valor Estimado:</label>
                        <p class="form-control-plaintext text-success font-weight-bold">
                            R$ {{ number_format($ppp->estimativa_valor ?? 0, 2, ',', '.') }}
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Data Ideal de Aquisição:</label>
                        <p class="form-control-plaintext">
                            {{ $ppp->data_ideal_aquisicao ? \Carbon\Carbon::parse($ppp->data_ideal_aquisicao)->format('d/m/Y') : 'N/A' }}
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Status:</label>
                        <p class="form-control-plaintext">
                            <span class="badge badge-warning">
                                <i class="fas fa-clock mr-1"></i>Novo
                            </span>
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Criado em:</label>
                        <p class="form-control-plaintext">
                            {{ $ppp->created_at ? $ppp->created_at->format('d/m/Y H:i') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
            
            @if($ppp->observacoes)
            <div class="row">
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