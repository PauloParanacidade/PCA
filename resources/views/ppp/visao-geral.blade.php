@extends('ppp.layouts.lista-base', [
    'pageTitle' => 'Visão Geral',
    'cardIcon' => 'fas fa-eye'
])

@section('header-actions')
    <!-- Ações específicas para acompanhamento podem ser adicionadas aqui no futuro -->
@endsection

{{-- @section('filtros')
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="status_filter" class="form-label">Filtrar por Status:</label>
            <select class="form-select" id="status_filter" name="status_filter">
                <option value="">Todos os Status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" {{ request('status_filter') == $status->id ? 'selected' : '' }}>
                        {{ $status->nome }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="search" class="form-label">Buscar:</label>
            <input type="text" class="form-control" id="search" name="search" 
                   value="{{ request('search') }}" placeholder="Nome do item, descrição...">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="button" class="btn btn-primary me-2" onclick="aplicarFiltros()">
                <i class="fas fa-search"></i> Filtrar
            </button>
            <button type="button" class="btn btn-secondary" onclick="limparFiltros()">
                <i class="fas fa-times"></i> Limpar
            </button>
        </div>
    </div>
@endsection --}}
@section('filtros')
    <!-- Modal de Comentário para Exclusão -->
    <div class="modal fade" id="comentarioExclusaoModal" tabindex="-1" role="dialog" aria-labelledby="comentarioExclusaoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="comentarioExclusaoModalLabel">
                        <i class="fas fa-comment-alt mr-2"></i>Comentário Obrigatório para Exclusão
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>PPP:</strong> <span id="nomeItemExclusaoComentario"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="comentarioExclusao">Motivo da exclusão <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="comentarioExclusao" rows="4" 
                                placeholder="Descreva o motivo da exclusão deste PPP (mínimo 10 caracteres)..."
                                maxlength="1000"></textarea>
                        <div class="invalid-feedback">
                            O comentário é obrigatório e deve ter pelo menos 10 caracteres.
                        </div>
                        <small class="form-text text-muted">
                            Este comentário será registrado no histórico do PPP.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" onclick="validarComentarioEProsseguir()">
                        <i class="fas fa-arrow-right mr-1"></i>Prosseguir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação Final -->
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
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                    <form id="formExclusaoFinal" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" id="comentarioExclusaoHidden" name="comentario">
                        <input type="hidden" name="origem" value="visao-geral">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-1"></i>Confirmar Exclusão Definitiva
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('table-headers')
<div class="table-header-row">
    <div class="table-header-col text-center" style="width: 5%;">#</div>
    <div class="table-header-col sortable" data-column="nome_item" style="width: 25%;">Nome do Item</div>
    <div class="table-header-col sortable text-center" data-column="prioridade" style="width: 10%;">Prioridade</div>
    <div class="table-header-col sortable text-center" data-column="area_solicitante" style="width: 10%;">Área Solicitante</div>
    <div class="table-header-col sortable text-center" data-column="gestor_atual" style="width: 14%;">Gestor Atual</div>
    <div class="table-header-col sortable text-center" data-column="status" style="width: 12%;">Status</div>
    <div class="table-header-col sortable text-right" data-column="valor_estimado" style="width: 12%; padding-right: 15px;">Valor Estimado</div>
    <div class="table-header-col text-center" style="width: 12%;">Ações</div>
</div>
@stop

@section('tabela-content')
    @if($ppps->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <tbody>
                    @foreach($ppps as $ppp)
                        <tr class="ppp-row" data-ppp-id="{{ $ppp->id }}" style="cursor: pointer;">
                            <td class="align-middle font-weight-bold text-center" style="width: 5%;">{{ $loop->iteration }}</td>  {{-- Coluna # --}}
                            <td class="text-left" style="width: 25%;"> {{-- Coluna Nome do Item --}}
                                <strong>{{ $ppp->nome_item }}</strong>
                                @if($ppp->descricao)
                                    <br><small class="text-muted">{{ Str::limit($ppp->descricao, 60) }}</small>
                                @endif
                            </td>
                            <td class="align-middle text-center" style="width: 10%;"> {{-- Coluna Prioridade --}}
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
                            <td class="align-middle text-center" style="width: 10%;"> {{-- Coluna Sigla da Área solicitante --}}
                                <span class="badge badge-secondary">
                                    {{ $ppp->user->department ?? 'Área N/A' }}
                                </span>
                            </td>
                            <td class="align-middle text-center" style="width: 14%;"> {{-- Coluna Gestor atual --}}
                                <span class="badge badge-info">
                                    {{ $ppp->current_approver }}
                                </span>
                            </td>
                            <td class="align-middle text-center" style="width: 12%;"> {{-- Coluna Status --}}
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
                            <td class="align-middle text-right" style="width: 12%; padding-right: 60px;"> {{-- Coluna Valor estimado --}}
                                <span class="text-success font-weight-bold">
                                    R$ {{ number_format($ppp->estimativa_valor ?? 0, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="align-middle text-center" style="width: 12%;"> {{-- Coluna Ações --}}
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
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Nenhum PPP encontrado para acompanhamento</h5>
            <p class="text-muted">Não há PPPs disponíveis para monitoramento no momento.</p>
        </div>
    @endif
@endsection

@section('card-actions')
    @if($ppps->count() == 0)
        <div class="text-center">
            <p class="text-muted mb-0">Aguardando PPPs para acompanhamento...</p>
        </div>
    @endif
@endsection

@section('modals')
    <!-- Modal de Visualização do PPP -->
    <div class="modal fade" id="visualizarPppModal" tabindex="-1" aria-labelledby="visualizarPppModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="visualizarPppModalLabel">Visualizar PPP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="visualizarPppContent">
                    <!-- Conteúdo será carregado via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Histórico -->
    <div class="modal fade" id="historicoModal" tabindex="-1" aria-labelledby="historicoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historicoModalLabel">Histórico do PPP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="historicoContent">
                    <!-- Conteúdo será carregado via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra-css')
    <style>
        .table th {
            border-top: none;
            font-weight: 600;
        }
        
        .badge {
            font-size: 0.75em;
            padding: 0.375rem 0.75rem;
        }
        
        .btn-group .btn {
            border-radius: 0.25rem;
            margin-right: 0.25rem;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        
        .table-responsive {
            border-radius: 0.375rem;
            overflow: hidden;
        }
        
        .table-dark th {
            background-color: #495057;
            border-color: #495057;
        }
        
        .table-striped > tbody > tr:nth-of-type(odd) > td {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .table-hover > tbody > tr:hover > td {
            background-color: rgba(0, 0, 0, 0.075);
        }
    </style>
@stop

@section('extra-js')
    <script>
        function visualizarPpp(pppId) {
            // Carregar conteúdo do PPP via AJAX
            fetch(`/ppp/${pppId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('visualizarPppContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('visualizarPppModal')).show();
                })
                .catch(error => {
                    console.error('Erro ao carregar PPP:', error);
                    alert('Erro ao carregar os dados do PPP.');
                });
        }
        
        function abrirHistorico(pppId) {
            // Carregar histórico via AJAX
            fetch(`/ppp/${pppId}/historico`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('historicoContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('historicoModal')).show();
                })
                .catch(error => {
                    console.error('Erro ao carregar histórico:', error);
                    alert('Erro ao carregar o histórico do PPP.');
                });
        }
        
        // Funcionalidades de filtros, ordenação e exclusão são herdadas do layout base
    </script>
@stop