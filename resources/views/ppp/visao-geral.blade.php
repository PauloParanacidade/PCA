@extends('ppp.layouts.lista-base', [
    'pageTitle' => 'Visão Geral',
'cardTitle' => 'Visão Geral',
    'cardIcon' => 'fas fa-eye',
    'cardHeaderClass' => 'bg-info'
])

@section('header-actions')
    <!-- Ações específicas para acompanhamento podem ser adicionadas aqui no futuro -->
@endsection

@section('filtros')
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
@endsection

@section('tabela-content')
    @if($ppps->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Item</th>
                        <th>Criado por</th>
                        <th>Gestor Atual</th>
                        <th>Status</th>
                        <th>Última Alteração</th>
                        <th>Valor Estimado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ppps as $ppp)
                        <tr class="ppp-row" data-ppp-id="{{ $ppp->id }}" style="cursor: pointer;">
                            <td>
                                <strong>{{ $ppp->nome_item }}</strong>
                                @if($ppp->descricao)
                                    <br><small class="text-muted">{{ Str::limit($ppp->descricao, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $ppp->user->name ?? 'N/A' }}
                                @if($ppp->user && $ppp->user->setor)
                                    <br><small class="text-muted">{{ $ppp->user->setor }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $ppp->gestorAtual->name ?? 'N/A' }}
                                @if($ppp->gestorAtual && $ppp->gestorAtual->department)
                                    <br><small class="text-muted">{{ $ppp->gestorAtual->department }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $ppp->status->cor ?? '#6c757d' }}">
                                    {{ $ppp->status->nome ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                {{ $ppp->updated_at ? $ppp->updated_at->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td>
                                R$ {{ number_format($ppp->valor_total_estimado ?? 0, 2, ',', '.') }}
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="visualizarPpp({{ $ppp->id }})" 
                                            title="Visualizar PPP">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="abrirHistorico({{ $ppp->id }})" 
                                            title="Ver Histórico">
                                        <i class="fas fa-history"></i>
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
        function aplicarFiltros() {
            const status = document.getElementById('status_filter').value;
            const search = document.getElementById('search').value;
            
            const params = new URLSearchParams();
            if (status) params.append('status_filter', status);
            if (search) params.append('search', search);
            
            const url = '{{ route("ppp.visao-geral") }}' + (params.toString() ? '?' + params.toString() : '');
            window.location.href = url;
        }
        
        function limparFiltros() {
            window.location.href = '{{ route("ppp.visao-geral") }}';
        }
        
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
        
        // Aplicar filtros ao pressionar Enter nos campos de busca
        document.getElementById('search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                aplicarFiltros();
            }
        });
        
        // Aplicar filtros ao alterar o select de status
        document.getElementById('status_filter').addEventListener('change', function() {
            aplicarFiltros();
        });
        
        // Clique em qualquer parte da linha do PPP para visualizar
        document.addEventListener('click', function(e) {
            const pppRow = e.target.closest('.ppp-row');
            if (pppRow && !e.target.closest('button')) {
                const pppId = pppRow.dataset.pppId;
                console.log('🔗 Redirecionando para PPP:', pppId);
                window.location.href = '{{ route("ppp.show", ":id") }}'.replace(':id', pppId);
            }
        });
        
        // Inicialização da página
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Página Visão Geral carregada');
        });
    </script>
@stop