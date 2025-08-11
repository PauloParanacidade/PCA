@extends('ppp.layouts.lista-base', [
    'pageTitle' => 'Visão Geral',
    'cardTitle' => 'Visão Geral',
    'cardIcon' => 'fas fa-eye',
    'cardHeaderClass' => 'bg-primary'
])

@section('header-actions')
    <!-- Ações específicas para acompanhamento podem ser adicionadas aqui no futuro -->
@endsection

@section('filtros')
    <div class="card shadow-sm mb-4">
        <div class="card-body bg-light">
            <div class="row">
                <div class="col-md-4">
                    <label for="status_filter" class="form-label fw-semibold text-dark">Filtrar por Status:</label>
                    <select class="form-select border-2" id="status_filter" name="status_filter">
                        <option value="">Todos os Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ request('status_filter') == $status->id ? 'selected' : '' }}>
                                {{ $status->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label fw-semibold text-dark">Buscar:</label>
                    <input type="text" class="form-control border-2" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Nome do item, descrição...">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-primary me-2 px-4" onclick="aplicarFiltros()">
                        <i class="fas fa-search me-1"></i> Filtrar
                    </button>
                    <button type="button" class="btn btn-outline-secondary px-4" onclick="limparFiltros()">
                        <i class="fas fa-times me-1"></i> Limpar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('tabela-content')
    @if($ppps->count() > 0)
        <div class="table-responsive shadow-sm">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-primary">
                    <tr>
                        <th class="fw-bold text-white">Item</th>
                        <th class="fw-bold text-white">Criado por</th>
                        <th class="fw-bold text-white">Gestor Atual</th>
                        <th class="fw-bold text-white">Status</th>
                        <th class="fw-bold text-white">Última Alteração</th>
                        <th class="fw-bold text-white">Valor Estimado</th>
                        <th class="fw-bold text-white">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ppps as $ppp)
                        <tr class="ppp-row align-middle" data-ppp-id="{{ $ppp->id }}" style="cursor: pointer;">
                            <td class="py-3">
                                <div class="fw-bold text-dark mb-1">{{ $ppp->nome_item }}</div>
                                @if($ppp->descricao_item)
                                    <small class="text-secondary">{{ Str::limit($ppp->descricao_item, 50) }}</small>
                                @endif
                            </td>
                            <td class="py-3">
                                <div class="text-dark">{{ $ppp->user->name ?? 'N/A' }}</div>
                                @if($ppp->user && $ppp->user->nomeSetor)
                                    <small class="text-secondary">{{ $ppp->user->nomeSetor }}</small>
                                @endif
                            </td>
                            <td class="py-3">
                                <div class="text-dark">{{ $ppp->gestorAtual->name ?? 'N/A' }}</div>
                                @if($ppp->gestorAtual && $ppp->gestorAtual->nomeSetor)
                                    <small class="text-secondary">{{ $ppp->gestorAtual->nomeSetor }}</small>
                                @endif
                            </td>
                            <td class="py-3">
                                <span class="badge fs-6 px-3 py-2" style="background-color: {{ $ppp->status->cor ?? '#6c757d' }}; color: white;">
                                    {{ $ppp->status->nome ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="py-3 text-dark">
                                {{ $ppp->updated_at ? $ppp->updated_at->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="py-3 fw-semibold text-success">
                                R$ {{ number_format($ppp->estimativa_valor ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="py-3">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('ppp.show', $ppp->id) }}?origem=visao-geral" 
                                       class="btn btn-sm btn-primary" 
                                       onclick="event.stopPropagation()" 
                                       title="Visualizar PPP">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="event.stopPropagation(); abrirHistorico({{ $ppp->id }})" 
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
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-search fa-4x text-primary opacity-50"></i>
                </div>
                <h5 class="text-dark mb-3">Nenhum PPP encontrado para acompanhamento</h5>
                <p class="text-secondary mb-0">Não há PPPs disponíveis para monitoramento no momento.</p>
            </div>
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
        /* Cores principais profissionais */
        :root {
            --primary-color: #2c5aa0;
            --primary-dark: #1e3d6f;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        /* Cabeçalho da tabela */
        .table-primary th {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
            font-weight: 600;
            padding: 1rem 0.75rem;
            border-top: none;
        }
        
        /* Linhas da tabela */
        .table-striped > tbody > tr:nth-of-type(odd) > td {
            background-color: rgba(44, 90, 160, 0.03);
        }
        
        .table-hover > tbody > tr:hover > td {
            background-color: rgba(44, 90, 160, 0.08);
            transition: background-color 0.2s ease;
        }
        
        /* Container da tabela */
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        /* Badges de status */
        .badge {
            font-size: 0.8em;
            font-weight: 500;
            letter-spacing: 0.5px;
            border-radius: 0.375rem;
        }
        
        /* Botões de ação */
        .btn-group .btn {
            border-radius: 0.375rem;
            margin-right: 0.25rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-1px);
        }
        
        .btn-outline-secondary:hover {
            transform: translateY(-1px);
        }
        
        /* Cards e containers */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Formulários */
        .form-control, .form-select {
            border-radius: 0.375rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.25);
        }
        
        /* Labels */
        .form-label {
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        /* Texto de valores */
        .text-success {
            color: var(--success-color) !important;
        }
        
        /* Sombras */
        .shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .btn-group .btn {
                padding: 0.25rem 0.5rem;
            }
        }
        
        /* Estados de hover para linhas */
        .ppp-row:hover {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        /* Melhorias de acessibilidade */
        .btn:focus {
            box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.25);
        }
        
        /* Espaçamento consistente */
        .py-3 {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
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
        
        // Inicialização da página
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Página Visão Geral carregada');
        });
    </script>
@stop