@extends('adminlte::page')

@section('title', 'PPPs - Plano de Contratação Anual')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-clipboard-list"></i> Plano de Contratação Anual (PPPs)</h1>
        <a href="{{ route('ppp.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Novo PPP
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Card de Filtros -->
    <div class="card card-outline card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('ppp.index') }}" class="row">
                <div class="col-md-3">
                    <label for="status">Status:</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Todos os Status</option>
                        <option value="novo" {{ request('status') == 'novo' ? 'selected' : '' }}>Novo</option>
                        <option value="em_analise" {{ request('status') == 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                        <option value="aprovado" {{ request('status') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                        <option value="rejeitado" {{ request('status') == 'rejeitado' ? 'selected' : '' }}>Rejeitado</option>
                        <option value="correcao" {{ request('status') == 'correcao' ? 'selected' : '' }}>Correção Solicitada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="setor">Setor:</label>
                    <select name="setor" id="setor" class="form-control">
                        <option value="">Todos os Setores</option>
                        <option value="meus_ppps" {{ request('setor') == 'meus_ppps' ? 'selected' : '' }}>Meus PPPs</option>
                        <option value="meu_setor" {{ request('setor') == 'meu_setor' ? 'selected' : '' }}>Meu Setor</option>
                        <option value="subordinados" {{ request('setor') == 'subordinados' ? 'selected' : '' }}>Setores Subordinados</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="busca">Buscar:</label>
                    <input type="text" name="busca" id="busca" class="form-control" 
                           placeholder="Nome do item ou descrição" value="{{ request('busca') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('ppp.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Card Principal -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Lista de PPPs 
                @if($ppps->count() > 0)
                    <span class="badge badge-primary">{{ $ppps->count() }} {{ $ppps->count() == 1 ? 'item' : 'itens' }}</span>
                @endif
            </h3>
        </div>
        <div class="card-body p-0">
            @if($ppps->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Nenhum PPP encontrado</h4>
                    <p class="text-muted">Você ainda não possui PPPs cadastrados ou nenhum PPP atende aos filtros aplicados.</p>
                    <a href="{{ route('ppp.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Criar Primeiro PPP
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 25%">Nome do Item</th>
                                <th style="width: 35%">Descrição</th>
                                <th style="width: 10%" class="text-center">Status</th>
                                <th style="width: 10%" class="text-center">Valor Estimado</th>
                                <th style="width: 15%" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ppps as $index => $ppp)
                                <tr>
                                    <td class="align-middle">
                                        <strong>{{ $index + 1 }}</strong>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold">{{ $ppp->nome_item }}</div>
                                        <small class="text-muted">{{ $ppp->area_solicitante }}</small>
                                    </td>
                                    <td class="align-middle">
                                        <div class="text-truncate" style="max-width: 300px;" title="{{ $ppp->descricao }}">
                                            {{ $ppp->descricao }}
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-secondary">Novo</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <strong class="text-success">
                                            R$ {{ number_format($ppp->estimativa_valor ?? 0, 2, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('ppp.show', $ppp->id) }}" 
                                               class="btn btn-sm btn-info" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('ppp.edit', $ppp->id) }}" 
                                               class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-secondary" 
                                                    data-toggle="modal" data-target="#historicoModal-{{ $ppp->id }}" 
                                                    title="Histórico">
                                                <i class="fas fa-history"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="confirmarExclusao({{ $ppp->id }}, '{{ $ppp->nome_item }}')" 
                                                    title="Remover">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Modais de Histórico -->
    @foreach($ppps as $ppp)
        <div class="modal fade" id="historicoModal-{{ $ppp->id }}" tabindex="-1" role="dialog" aria-labelledby="historicoModalLabel-{{ $ppp->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="historicoModalLabel-{{ $ppp->id }}">
                            <i class="fas fa-history"></i> Histórico do PPP: {{ $ppp->nome_item }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="timeline">
                            <div class="time-label">
                                <span class="bg-primary">Histórico de Movimentações</span>
                            </div>
                            <div>
                                <i class="fas fa-plus bg-success"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $ppp->created_at->format('d/m/Y H:i') }}</span>
                                    <h3 class="timeline-header">PPP Criado</h3>
                                    <div class="timeline-body">
                                        PPP criado pelo usuário {{ $ppp->user->name ?? 'Sistema' }}
                                    </div>
                                </div>
                            </div>
                            <div>
                                <i class="fas fa-clock bg-gray"></i>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i> 
                            O histórico completo será implementado quando o sistema de status estiver ativo.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="modalExclusao" tabindex="-1" role="dialog" aria-labelledby="modalExclusaoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalExclusaoLabel">
                        <i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Você tem certeza que deseja remover o PPP <strong id="nomeItemExclusao"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Esta ação não pode ser desfeita!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <form id="formExclusao" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Confirmar Exclusão
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
function confirmarExclusao(id, nomeItem) {
    document.getElementById('nomeItemExclusao').textContent = nomeItem;
    document.getElementById('formExclusao').action = '/ppp/' + id;
    $('#modalExclusao').modal('show');
}

// Auto-hide alerts after 5 seconds
$(document).ready(function() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush
@endsection
