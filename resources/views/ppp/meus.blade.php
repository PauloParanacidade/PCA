@extends('ppp.layouts.lista-base', [
    'pageTitle' => 'Meus PPPs',
    'cardIcon' => 'fas fa-user-edit'
])

@section('header-actions')
    <a href="{{ route('ppp.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i>Novo PPP
    </a>
@stop

@section('card-actions')
    @if($ppps->count() == 0)
        <a href="{{ route('ppp.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>Criar Primeiro PPP
        </a>
    @endif
@stop

@section('table-headers')
<div class="table-header-row">
    <div class="table-header-col" style="width: 5%;">#</div>
    <div class="table-header-col sortable" data-column="nome_item" style="width: 28%;">Nome do Item</div>
    <div class="table-header-col sortable" data-column="prioridade" style="width: 10%;">Prioridade</div>
    <div class="table-header-col sortable" data-column="area_solicitante" style="width: 12%;">Área Solicitante</div>
    <div class="table-header-col sortable" data-column="status" style="width: 12%;">Status</div>
    <div class="table-header-col sortable" data-column="avaliador" style="width: 12%;">Avaliador</div>
    <div class="table-header-col sortable" data-column="valor_estimado" style="width: 11%;">Valor Estimado</div>
    <div class="table-header-col" style="width: 10%;">Ações</div>
</div>
@stop

@section('tabela-content')
@if($ppps->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <tbody>
                            @foreach($ppps as $ppp)
                                <tr class="ppp-row text-center" data-ppp-id="{{ $ppp->id }}" style="cursor: pointer;">
                                    <td class="align-middle font-weight-bold" style="width: 5%;">{{ $loop->iteration }}</td> 
                                    <td class="align-middle text-left" style="width: 28%;">  {{-- Coluna Nome do Item --}}
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-bold">{{ $ppp->nome_item }}</span>
                                            @if($ppp->descricao)
                                                <small class="text-muted">{{ Str::limit($ppp->descricao, 60) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-middle" style="width: 10%;"> {{-- Coluna Prioridade --}}
                                        @if($ppp->grau_prioridade)
                                            <span class="badge 
                                                @if($ppp->grau_prioridade === 'Alta' || $ppp->grau_prioridade === 'Urgente') badge-danger
                                                @elseif($ppp->grau_prioridade === 'Média') badge-warning
                                                @else badge-success
                                                @endif">
                                                @if($ppp->grau_prioridade === 'Alta' || $ppp->grau_prioridade === 'Urgente') 🔴
                                                @elseif($ppp->grau_prioridade === 'Média') 🟡
                                                @else 🟢
                                                @endif
                                                {{ $ppp->grau_prioridade }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td class="align-middle" style="width: 12%;"> {{-- Coluna Sigla da Área solicitante --}}
                                        <span class="badge badge-secondary">
                                            {{ $ppp->user->department ?? 'Área N/A' }}
                                        </span>
                                    </td>
                                    <td class="align-middle" style="width: 12%;"> {{-- Coluna Status --}}
                                        <div class="d-flex flex-column">
                                            <span class="badge badge-info mb-1">
                                                @if($ppp->status)
                                                    <i class="fas fa-info-circle mr-1"></i>{{ $ppp->status->nome }}
                                                @else
                                                    <i class="fas fa-info-circle mr-1"></i>Status não definido
                                                @endif
                                            </span>
                                            <small class="text-muted">
                                                {{ $ppp->ultima_mudanca_status ? $ppp->ultima_mudanca_status->format('d/m/Y H:i') : 'N/A' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td class="align-middle" style="width: 12%;"> {{-- Coluna Avaliador --}}
                                        <span class="badge badge-info">
                                            {{ $ppp->current_approver }}
                                        </span>
                                    </td>
                                    <td class="align-middle" style="width: 11%;"> {{-- Coluna Valor estimado --}}
                                        <span class="text-success font-weight-bold">
                                            R$ {{ number_format($ppp->estimativa_valor ?? 0, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center" style="width: 10%;"> {{-- Coluna Ações --}}
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('ppp.show', $ppp->id) }}?origem=meus" class="btn btn-sm btn-outline-info" title="Visualizar" onclick="event.stopPropagation();">
                                                <i class="fas fa-eye"></i>  {{-- Ver o PPP --}}
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="event.stopPropagation(); confirmarExclusao({{ $ppp->id }}, '{{ addslashes($ppp->nome_item) }}')" title="Remover">
                                                <i class="fas fa-trash"></i> {{-- Apagar o PPP --}}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
 @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum PPP encontrado</h5>
                    <p class="text-muted mb-4">Você ainda não criou nenhum PPP ou nenhum PPP corresponde aos filtros aplicados.</p>
                    <a href="{{ route('ppp.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus mr-2"></i>Criar Meu Primeiro PPP
                    </a>
                </div>
 @endif
@stop

@section('modals')
<!-- Modais de Histórico -->
<!-- Modal único reutilizável para histórico -->
<div class="modal fade" id="historicoModal" tabindex="-1" role="dialog" aria-labelledby="historicoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="historicoModalTitle">
                    <i class="fas fa-history mr-2"></i>Histórico do PPP
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="historicoModalBody">
                <div class="text-center py-3">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Carregando histórico...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Comentário Obrigatório para Exclusão -->
<div class="modal fade" id="comentarioExclusaoModal" tabindex="-1" role="dialog" aria-labelledby="comentarioExclusaoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="comentarioExclusaoModalLabel">
                    <i class="fas fa-comment-alt mr-2"></i>Comentário para Exclusão
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Você está prestes a excluir o PPP <strong id="nomeItemExclusaoComentario"></strong>.
                </div>
                
                <div class="form-group">
                    <label for="comentarioExclusao">Motivo da exclusão <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="comentarioExclusao" name="comentario" rows="4" 
                            placeholder="Descreva o motivo da exclusão deste PPP..." required></textarea>
                    <small class="form-text text-muted">
                        Este comentário será registrado no histórico do PPP antes da exclusão.
                    </small>
                    <div class="invalid-feedback" id="comentarioExclusaoError">
                        O comentário é obrigatório para prosseguir com a exclusão.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-warning" onclick="validarComentarioEProsseguir()">
                    <i class="fas fa-save mr-1"></i>Salvar mensagem e excluir definitivamente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Confirmação Final de Exclusão -->
<div class="modal fade" id="confirmacaoFinalExclusaoModal" tabindex="-1" role="dialog" aria-labelledby="confirmacaoFinalExclusaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmacaoFinalExclusaoModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirmação Final de Exclusão
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h6><i class="fas fa-info-circle mr-2"></i>Importante: Diferença entre Reprovar e Excluir</h6>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success"><i class="fas fa-times-circle mr-1"></i>Reprovar PPP:</h6>
                            <ul class="mb-0">
                                <li>PPP permanece disponível para consultas futuras</li>
                                <li>Histórico é mantido</li>
                                <li>Pode ser visualizado posteriormente</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-danger"><i class="fas fa-trash mr-1"></i>Excluir PPP:</h6>
                            <ul class="mb-0">
                                <li><strong>Elimina o PPP do sistema permanentemente</strong></li>
                                <li>Não pode ser recuperado</li>
                                <li>Histórico será perdido</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb mr-2"></i>Quer apenas cancelar o PPP?</h6>
                    <p class="mb-0">
                        <strong>Se desejar apenas cancelar mantendo ele no histórico</strong>, vá para o menu do card roxo, dentro do PPP, e use a opção <strong>"Reprovar"</strong>.
                    </p>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Tem certeza que deseja excluir definitivamente o PPP "<span id="nomeItemConfirmacaoFinal"></span>"?</strong>
                    <br><small class="text-muted">Esta ação não poderá ser desfeita e o PPP não estará mais disponível no sistema.</small>
                </div>
                
                <div class="bg-light p-3 rounded">
                    <h6><i class="fas fa-comment mr-2"></i>Comentário registrado:</h6>
                    <p class="mb-0 font-italic" id="comentarioRegistrado"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                </button>
                <form id="formExclusaoFinal" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" id="comentarioExclusaoHidden" name="comentario">
                    <input type="hidden" name="origem" value="meus">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i>Confirmar Exclusão Definitiva
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('extra-css')
<style>
    .card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .card-header {
        border-bottom: none;
        padding: 20px;
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .ppp-row:hover {
        background-color: rgba(0, 123, 255, 0.1) !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
    }
    
    .btn-group .btn {
        margin: 0 1px;
    }
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -22px;
        top: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #dee2e6;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }

    .timeline-title {
        margin: 0 0 8px 0;
        font-weight: 600;
        color: #495057;
    }

    .timeline-text {
        margin: 0 0 8px 0;
        color: #6c757d;
    }

    .bg-orange {
        background-color: #fd7e14 !important;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
    }
</style>
@stop

{{-- Scripts são herdados do layout base --}}