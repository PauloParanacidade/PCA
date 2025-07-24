@extends('layouts.adminlte-custom')

@section('title', 'Meus PPPs')

@section('content_header')
    @parent
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-list mr-2"></i>Meus PPPs</h1>
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

    <!-- Card Principal -->
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>
                Lista dos meus PPPs criados
            </h3>
        </div>
        
        <div class="card-body">
            <!-- Filtros -->
            <form method="GET" action="{{ route('ppp.meus') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status_id">Filtrar por Status:</label>
                            <select name="status_id" id="status_id" class="form-control">
                                <option value="">Todos os status</option>
                                <option value="1" {{ request('status_id') == '1' ? 'selected' : '' }}>Rascunho</option>
                                <option value="2" {{ request('status_id') == '2' ? 'selected' : '' }}>Aguardando Aprovação</option>
                                <option value="3" {{ request('status_id') == '3' ? 'selected' : '' }}>Em Avaliação</option>
                                <option value="4" {{ request('status_id') == '4' ? 'selected' : '' }}>Aguardando Correção</option>
                                <option value="5" {{ request('status_id') == '5' ? 'selected' : '' }}>Em Correção</option>
                                <option value="6" {{ request('status_id') == '6' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="busca">Buscar:</label>
                            <input type="text" name="busca" id="busca" class="form-control" 
                                   value="{{ request('busca') }}" placeholder="Nome do item ou descrição">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i>Filtrar
                                </button>
                                <a href="{{ route('ppp.meus') }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i>Limpar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Tabela -->
            @if($ppps->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Nome do Item</th>
                                <th>Status</th>
                                <th>Prioridade</th>
                                <th>Valor Estimado</th>
                                <th>Criado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ppps as $ppp)
                                <tr>
                                    <td>{{ $ppp->id }}</td>
                                    <td>{{ $ppp->nome_item }}</td>
                                    <td>
                                        <span class="badge badge-{{ $ppp->status->cor ?? 'secondary' }}">
                                            {{ $ppp->status->nome ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $ppp->grau_prioridade === 'Alta' ? 'danger' : ($ppp->grau_prioridade === 'Média' ? 'warning' : 'info') }}">
                                            {{ $ppp->grau_prioridade }}
                                        </span>
                                    </td>
                                    <td>R$ {{ number_format($ppp->estimativa_valor, 2, ',', '.') }}</td>
                                    <td>{{ $ppp->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('ppp.show', $ppp->id) }}" 
                                               class="btn btn-sm btn-info" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($ppp->status_id == 1 || $ppp->status_id == 4)
                                                <a href="{{ route('ppp.edit', $ppp->id) }}" 
                                                   class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <div class="d-flex justify-content-center">
                    {{ $ppps->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    Você ainda não criou nenhum PPP.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection