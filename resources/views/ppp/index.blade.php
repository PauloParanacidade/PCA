@extends('layouts.adminlte-custom')

@section('title', 'Meus PPPs')

@section('content_header')
    @parent
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-check mr-2"></i>Meus PPPs</h1>
        <a href="{{ route('ppp.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>Novo PPP
        </a>
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Card de Filtros -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-filter mr-2"></i>Filtros de Busca
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('ppp.index') }}" class="filters-form">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status" class="form-label">Status:</label>
                            <select name="status" id="status" class="form-control form-control-lg">
                                <option value="">Todos os Status</option>
                                <option value="novo" {{ request('status') == 'novo' ? 'selected' : '' }}>Novo</option>
                                <option value="em_analise" {{ request('status') == 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                                <option value="aprovado" {{ request('status') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                                <option value="rejeitado" {{ request('status') == 'rejeitado' ? 'selected' : '' }}>Rejeitado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="setor" class="form-label">Setor:</label>
                            <select name="setor" id="setor" class="form-control form-control-lg">
                                <option value="">Todos os Setores</option>
                                <option value="TI" {{ request('setor') == 'TI' ? 'selected' : '' }}>TI</option>
                                <option value="RH" {{ request('setor') == 'RH' ? 'selected' : '' }}>RH</option>
                                <option value="Financeiro" {{ request('setor') == 'Financeiro' ? 'selected' : '' }}>Financeiro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="busca" class="form-label">Buscar por Nome ou Descrição:</label>
                            <input type="text" name="busca" id="busca" class="form-control form-control-lg" 
                                   placeholder="Digite para buscar..." value="{{ request('busca') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex flex-column">
                                <button type="submit" class="btn btn-primary btn-lg mb-2">
                                    <i class="fas fa-search mr-1"></i>Filtrar
                                </button>
                                <a href="{{ route('ppp.index') }}" class="btn-clear">
                                    <i class="fas fa-times mr-1"></i>Limpar Filtros
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Card Principal -->
    <div class="card shadow-sm">
        <div class="card-header bg-gradient-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list mr-2"></i>Listagem de PPPs
                <small class="ml-2">({{ $ppps->total() }} {{ $ppps->total() == 1 ? 'item' : 'itens' }})</small>
            </h5>
            @if($ppps->count() == 0)
                <a href="{{ route('ppp.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i>Criar Primeiro PPP
                </a>
            @endif
        </div>
        <div class="card-body p-0">
            @if($ppps->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Nome do Item</th>
                                <th width="20%">Área Solicitante</th>
                                <th width="15%">Valor Estimado</th>
                                <th width="10%">Status</th>
                                <th width="15%">Data Criação</th>
                                <th width="10%" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ppps as $ppp)
                                <tr class="ppp-row" data-ppp-id="{{ $ppp->id }}" style="cursor: pointer;">
                                    <td class="align-middle font-weight-bold">#{{ $ppp->id }}</td>
                                    <td class="align-middle">
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-bold">{{ $ppp->nome_item }}</span>
                                            @if($ppp->descricao)
                                                <small class="text-muted">{{ Str::limit($ppp->descricao, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-secondary">{{ $ppp->user->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="text-success font-weight-bold">
                                            R$ {{ number_format($ppp->estimativa_valor ?? 0, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    
                                    <td class="align-middle">
                                        <span class="badge badge-info">
                                            <!-- ✅ CORRIGIR: Usar o relacionamento status correto -->
                                            @if($ppp->status)
                                            <i class="fas fa-info-circle mr-1"></i>{{ $ppp->status->nome }}
                                            @else
                                            <i class="fas fa-info-circle mr-1"></i>Status não definido
                                            @endif
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <small>{{ $ppp->created_at ? $ppp->created_at->format('d/m/Y H:i') : 'N/A' }}</small>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('ppp.show', $ppp->id) }}" class="btn btn-sm btn-outline-info" title="Visualizar" onclick="event.stopPropagation();">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('ppp.edit', $ppp->id) }}" class="btn btn-sm btn-outline-warning" title="Editar" onclick="event.stopPropagation();">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    data-toggle="modal" data-target="#historicoModal{{ $ppp->id }}" title="Histórico" onclick="event.stopPropagation();">
                                                <i class="fas fa-history"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="event.stopPropagation(); confirmarExclusao({{ $ppp->id }}, '{{ $ppp->nome_item }}')" title="Remover">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                @if($ppps->hasPages())
                    <div class="card-footer">
                        {{ $ppps->links() }}
                    </div>
                @endif
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
        </div>
    </div>
</div>

<!-- Modais de Histórico -->
@foreach($ppps as $ppp)
    <div class="modal fade" id="historicoModal{{ $ppp->id }}" tabindex="-1" role="dialog" aria-labelledby="historicoModalLabel{{ $ppp->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="historicoModalLabel{{ $ppp->id }}">
                        <i class="fas fa-history mr-2"></i>Histórico: {{ $ppp->nome_item }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($ppp->historicos && $ppp->historicos->count() > 0)
                        <div class="timeline">
                            @foreach($ppp->historicos as $historico)
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
@endforeach

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="confirmarExclusaoModal" tabindex="-1" role="dialog" aria-labelledby="confirmarExclusaoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmarExclusaoModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar Exclusão
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o PPP <strong id="nomeItemExclusao"></strong>?</p>
                <p class="text-muted">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </button>
                <form id="formExclusao" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i>Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .filters-form {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        padding: 20px;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 1.1em;
    }
    
    .form-control-lg {
        padding: 12px 16px;
        font-size: 1.1em;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        transition: all 0.3s ease;
    }
    
    .form-control-lg:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        transform: translateY(-1px);
    }
    
    .btn-clear {
        display: inline-block;
        padding: 8px 16px;
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.9em;
        transition: all 0.3s ease;
        border: none;
        text-align: center;
    }
    
    .btn-clear:hover {
        background: linear-gradient(135deg, #5a6268 0%, #343a40 100%);
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .card-header {
        border-bottom: none;
        padding: 20px;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
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
    function confirmarExclusao(id, nomeItem) {
        document.getElementById('nomeItemExclusao').textContent = nomeItem;
        document.getElementById('formExclusao').action = '/ppp/' + id;
        $('#confirmarExclusaoModal').modal('show');
    }
    
    $(document).ready(function() {
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Clique em qualquer parte da linha do PPP para visualizar
        $('.ppp-row').click(function() {
            var pppId = $(this).data('ppp-id');
            window.location.href = '{{ route("ppp.show", ":id") }}'.replace(':id', pppId);
        });
    });
</script>
@endsection
