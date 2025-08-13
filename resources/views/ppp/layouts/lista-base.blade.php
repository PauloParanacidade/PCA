@extends('adminlte::page')

@section('title', $pageTitle ?? 'PPPs')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="{{ $cardIcon ?? 'fas fa-list' }} mr-2"></i>
            {{ $pageTitle ?? 'PPPs' }}
        </h1>
        @yield('header-actions')
    </div>
@stop

@section('content')
    <!-- Banner de Impersonate -->
    <x-impersonate-banner />
    
    <div class="container-fluid">
        {{-- Alertas do sistema --}}
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

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        
        {{-- Seção de filtros centralizados - TEMPORARIAMENTE OCULTA --}}
        {{-- @if(isset($showFilters) && $showFilters)
        <div class="card mb-3">
            <div class="card-body">
                <form id="filtrosForm" method="GET">
                    <div class="row">
                        @if(isset($statusOptions))
                        <div class="col-md-3">
                            <label for="status_filter" class="form-label">Status:</label>
                            <select class="form-control" id="status_filter" name="status_filter">
                                <option value="">Todos os Status</option>
                                @foreach($statusOptions as $status)
                                    <option value="{{ $status->id }}" {{ request('status_filter') == $status->id ? 'selected' : '' }}>
                                        {{ $status->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        
                        <div class="col-md-3">
                            <label for="search" class="form-label">Buscar:</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Nome, descrição...">
                        </div>
                        
                        @if(isset($showPriorityFilter) && $showPriorityFilter)
                        <div class="col-md-3">
                            <label for="priority_filter" class="form-label">Prioridade:</label>
                            <select class="form-control" id="priority_filter" name="priority_filter">
                                <option value="">Todas as Prioridades</option>
                                <option value="Urgente" {{ request('priority_filter') == 'Urgente' ? 'selected' : '' }}>Urgente</option>
                                <option value="Alta" {{ request('priority_filter') == 'Alta' ? 'selected' : '' }}>Alta</option>
                                <option value="Média" {{ request('priority_filter') == 'Média' ? 'selected' : '' }}>Média</option>
                                <option value="Baixa" {{ request('priority_filter') == 'Baixa' ? 'selected' : '' }}>Baixa</option>
                            </select>
                        </div>
                        @endif
                        
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary me-2" onclick="aplicarFiltros()">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="limparFiltros()">
                                <i class="fas fa-times"></i> Limpar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif --}}
        
        {{-- Seção de filtros customizados --}}
        @yield('filtros')
        
        {{-- Card principal --}}
        <div class="card">
            <div class="card-header bg-gradient-primary">
                <div class="d-flex justify-content-between align-items-center">
                    @yield('table-headers')
                    @yield('card-actions')
                </div>
            </div>
            
            <div class="card-body p-0">
                @yield('tabela-content')
            </div>
            
            {{-- Paginação --}}
            @if(isset($ppps) && $ppps->hasPages())
                <div class="card-footer">
                    {{ $ppps->links() }}
                </div>
            @endif
        </div>
    </div>
    
    {{-- Modals Centralizados --}}
    <!-- Modal de Comentário para Exclusão -->
    <div class="modal fade" id="comentarioExclusaoModal" tabindex="-1" role="dialog" aria-labelledby="comentarioExclusaoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white" id="comentarioExclusaoModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Motivo da Exclusão
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Você está prestes a excluir o PPP:</p>
                    <p class="font-weight-bold text-primary" id="nomeItemExclusaoComentario"></p>
                    
                    <div class="form-group">
                        <label for="comentarioExclusao">Por favor, informe o motivo da exclusão (mínimo 10 caracteres):</label>
                        <textarea class="form-control" id="comentarioExclusao" rows="3" 
                                  placeholder="Descreva o motivo da exclusão deste PPP..."></textarea>
                        <div class="invalid-feedback">
                            O comentário é obrigatório e deve ter pelo menos 10 caracteres.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" onclick="validarComentarioEProsseguir()">
                        <i class="fas fa-arrow-right mr-1"></i>Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação Final da Exclusão -->
    <div class="modal fade" id="confirmacaoFinalExclusaoModal" tabindex="-1" role="dialog" aria-labelledby="confirmacaoFinalExclusaoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="confirmacaoFinalExclusaoModalLabel">
                        <i class="fas fa-trash-alt mr-2"></i>Confirmação Final
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>ATENÇÃO:</strong> Esta ação não pode ser desfeita!
                    </div>
                    
                    <p>Confirma a exclusão do PPP:</p>
                    <p class="font-weight-bold text-danger" id="nomeItemConfirmacaoFinal"></p>
                    
                    <p><strong>Motivo registrado:</strong></p>
                    <div class="bg-light p-2 rounded">
                        <em id="comentarioRegistrado"></em>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                    <form id="formExclusaoFinal" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" id="comentarioExclusaoHidden" name="comentario_exclusao">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt mr-1"></i>Confirmar Exclusão
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Modals Específicos das Páginas --}}
    @yield('modals')
@stop

@section('css')
    <style>
        /* Estilos para o banner de impersonamento */
        .impersonate-banner {
            margin: -15px -15px 20px -15px;
            border-radius: 0;
        }
        
        /* Ajuste para o content quando banner estiver ativo */
        .content {
            padding-top: 0;
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
        
        .bg-gradient-success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }
        
        /* Estilos para cabeçalhos de tabela na barra colorida */
        .table-header-row {
            display: flex;
            width: 100%;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .table-header-col {
            padding: 0 10px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .table-header-col:not(.no-sort):hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }
        
        .table-header-col.sortable::after {
            content: '\f0dc';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-left: 5px;
            opacity: 0.5;
            font-size: 0.8em;
        }
        
        .table-header-col.sort-asc::after {
            content: '\f0de';
            opacity: 1;
            color: #ffd700;
        }
        
        .table-header-col.sort-desc::after {
            content: '\f0dd';
            opacity: 1;
            color: #ffd700;
        }
        
        /* Remove o thead das tabelas filhas */
        .table thead {
            display: none;
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
            cursor: pointer;
        }
        
        .btn-group .btn {
            margin: 0 1px;
        }
        
        /* Timeline do histórico */
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
        
        /* Tabela desabilitada durante reunião */
        .tabela-desabilitada {
            pointer-events: none;
            opacity: 0.6;
            position: relative;
        }
        
        .tabela-desabilitada::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.7);
            z-index: 10;
        }
    </style>
    @yield('extra-css')
@stop

@section('js')
    @vite('resources/js/ppp-form.js')
    <script>
        // ===================================
        // JAVASCRIPT COMUM PARA LISTAS PPP
        // ===================================
        
        // Variáveis globais
        let pppParaExcluir = {
            id: null,
            nome: null
        };
        
        let currentSort = {
            column: null,
            direction: null // null, 'asc', 'desc'
        };
        
        // ===================================
        // FUNÇÕES DE FILTROS CENTRALIZADAS - TEMPORARIAMENTE DESABILITADAS
        // ===================================
        
        /*
        function aplicarFiltros() {
            const form = document.getElementById('filtrosForm');
            if (!form) return;
            
            const formData = new FormData(form);
            const params = new URLSearchParams();
            
            // Adicionar parâmetros de filtro
            for (let [key, value] of formData.entries()) {
                if (value.trim()) {
                    params.append(key, value);
                }
            }
            
            // Manter ordenação atual se existir
            if (currentSort.column && currentSort.direction) {
                params.append('sort', currentSort.column);
                params.append('direction', currentSort.direction);
            }
            
            // Redirecionar com filtros
            const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.location.href = url;
        }
        
        function limparFiltros() {
            // Limpar apenas filtros, manter ordenação
            const params = new URLSearchParams();
            if (currentSort.column && currentSort.direction) {
                params.append('sort', currentSort.column);
                params.append('direction', currentSort.direction);
            }
            
            const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.location.href = url;
        }
        */
        
        // ===================================
        // FUNÇÕES DE ORDENAÇÃO DAS COLUNAS
        // ===================================
        
        function initSortableColumns() {
            const headers = document.querySelectorAll('.table-header-col:not(.no-sort)');
            
            headers.forEach((header, index) => {
                // Adicionar classe sortable
                header.classList.add('sortable');
                
                // Adicionar evento de clique
                header.addEventListener('click', function() {
                    sortColumn(index, header);
                });
            });
            
            // Verificar se há ordenação ativa na URL
            const urlParams = new URLSearchParams(window.location.search);
            const sortColumn = urlParams.get('sort');
            const sortDirection = urlParams.get('direction');
            
            if (sortColumn && sortDirection) {
                currentSort.column = sortColumn;
                currentSort.direction = sortDirection;
                
                // Aplicar classe visual ao cabeçalho correspondente
                const headerIndex = parseInt(sortColumn);
                if (headers[headerIndex]) {
                    headers[headerIndex].classList.add(`sort-${sortDirection}`);
                }
            }
        }
        
        function sortColumn(columnIndex, headerElement) {
            // Remover classes de ordenação de todos os cabeçalhos
            document.querySelectorAll('.table-header-col').forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            
            // Determinar nova direção
            let newDirection;
            if (currentSort.column === columnIndex.toString()) {
                if (currentSort.direction === 'asc') {
                    newDirection = 'desc';
                } else if (currentSort.direction === 'desc') {
                    newDirection = null; // Voltar ao original
                } else {
                    newDirection = 'asc';
                }
            } else {
                newDirection = 'asc';
            }
            
            // Atualizar estado atual
            currentSort.column = newDirection ? columnIndex.toString() : null;
            currentSort.direction = newDirection;
            
            // Aplicar classe visual
            if (newDirection) {
                headerElement.classList.add(`sort-${newDirection}`);
            }
            
            // Aplicar ordenação
            applySorting();
        }
        
        function applySorting() {
            const params = new URLSearchParams(window.location.search);
            
            // Remover parâmetros de ordenação anteriores
            params.delete('sort');
            params.delete('direction');
            
            // Adicionar novos parâmetros se houver ordenação
            if (currentSort.column && currentSort.direction) {
                params.append('sort', currentSort.column);
                params.append('direction', currentSort.direction);
            }
            
            // Redirecionar
            const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.location.href = url;
        }
        
        // ===================================
        // FUNÇÕES DE EXCLUSÃO
        // ===================================
        
        function confirmarExclusao(id, nomeItem) {
            // Armazenar dados do PPP
            pppParaExcluir.id = id;
            pppParaExcluir.nome = nomeItem;
            
            // Limpar campos da modal anterior
            document.getElementById('comentarioExclusao').value = '';
            document.getElementById('comentarioExclusao').classList.remove('is-invalid');
            document.getElementById('nomeItemExclusaoComentario').textContent = nomeItem;
            
            // Abrir primeira modal
            $('#comentarioExclusaoModal').modal('show');
        }

        function validarComentarioEProsseguir() {
            const comentario = document.getElementById('comentarioExclusao').value.trim();
            
            if (!comentario || comentario.length < 10) {
                document.getElementById('comentarioExclusao').classList.add('is-invalid');
                return;
            }
            
            // Fechar primeira modal
            $('#comentarioExclusaoModal').modal('hide');
            
            // Aguardar fechamento e abrir segunda modal
            $('#comentarioExclusaoModal').on('hidden.bs.modal', function() {
                document.getElementById('nomeItemConfirmacaoFinal').textContent = pppParaExcluir.nome;
                document.getElementById('comentarioRegistrado').textContent = comentario;
                document.getElementById('comentarioExclusaoHidden').value = comentario;
                document.getElementById('formExclusaoFinal').action = `/ppp/${pppParaExcluir.id}`;
                $('#confirmacaoFinalExclusaoModal').modal('show');
                
                // Remover listener para evitar múltiplas execuções
                $(this).off('hidden.bs.modal');
            });
        }
        
        // ===================================
        // INICIALIZAÇÃO CENTRALIZADA
        // ===================================
        
        $(document).ready(function() {
            console.log('🚀 === INICIALIZAÇÃO DA LISTA PPP ===');
            
            // Debug: Verificar se elementos existem
            console.log('🔍 Verificações iniciais:');
            console.log('- Modal histórico existe:', $('#historicoModal').length > 0);
            console.log('- FormButtons existe:', typeof FormButtons !== 'undefined');
            console.log('- jQuery existe:', typeof $ !== 'undefined');
            console.log('- Bootstrap modal existe:', typeof $.fn.modal !== 'undefined');
            
            // Verificar se há PPPs na tabela
            const totalPpps = $('.ppp-row').length;
            console.log('- Total de PPPs na tabela:', totalPpps);
            
            // Inicializar ordenação das colunas
            initSortableColumns();
            
            // Inicializar eventos de filtros - TEMPORARIAMENTE DESABILITADO
            // initFilterEvents();
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert-success, .alert-danger, .alert-warning').not('.alert-info').fadeOut('slow');
            }, 5000);
            
            // Clique em qualquer parte da linha do PPP para visualizar
            $('.ppp-row').click(function() {
                // Verificar se tabela está desabilitada
                if ($('#tabelaPpps').hasClass('tabela-desabilitada')) {
                    return false;
                }
                
                var pppId = $(this).data('ppp-id');
                console.log('🔗 Redirecionando para PPP:', pppId);
                
                // Detectar a origem baseada na URL atual
                var origem = 'meus'; // padrão
                if (window.location.pathname.includes('/ppp/visao-geral')) {
                    origem = 'visao-geral';
                } else if (window.location.pathname === '/ppp' || window.location.pathname.includes('/ppp?')) {
                    origem = 'index';
                }
                
                window.location.href = '{{ route("ppp.show", ":id") }}'.replace(':id', pppId) + '?origem=' + origem;
            });
            
            // Executar inicialização específica da página se existir
            if (typeof initPageSpecific === 'function') {
                initPageSpecific();
            }
            
            // Log de inicialização completa
            console.log('✅ Inicialização da lista PPP concluída');
        });
        
        // ===================================
        // EVENTOS DE FILTROS - TEMPORARIAMENTE DESABILITADOS
        // ===================================
        
        /*
        function initFilterEvents() {
            // Aplicar filtros ao pressionar Enter nos campos de busca
            const searchField = document.getElementById('search');
            if (searchField) {
                searchField.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        aplicarFiltros();
                    }
                });
            }
            
            // Aplicar filtros ao alterar selects
            const statusFilter = document.getElementById('status_filter');
            if (statusFilter) {
                statusFilter.addEventListener('change', function() {
                    aplicarFiltros();
                });
            }
            
            const priorityFilter = document.getElementById('priority_filter');
            if (priorityFilter) {
                priorityFilter.addEventListener('change', function() {
                    aplicarFiltros();
                });
            }
        }
        */
    </script>
    @yield('extra-js')
@stop