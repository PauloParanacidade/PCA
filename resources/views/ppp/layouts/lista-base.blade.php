@extends('adminlte::page')

@section('title', $pageTitle ?? 'PPPs')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $pageTitle ?? 'PPPs' }}</h1>
        @yield('header-actions')
    </div>
@stop

@section('content')
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
        
        {{-- Seção de filtros --}}
        @yield('filtros')
        
        {{-- Card principal --}}
        <div class="card">
            <div class="card-header {{ $cardHeaderClass ?? 'bg-gradient-primary' }}">
                <h3 class="card-title text-white">
                    <i class="{{ $cardIcon ?? 'fas fa-list' }} mr-2"></i>
                    {{ $cardTitle ?? 'Lista de PPPs' }}
                </h3>
                @yield('card-actions')
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
    
    {{-- Modals --}}
    @yield('modals')
@stop

@section('css')
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
        
        .bg-gradient-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }
        
        .bg-gradient-info {
            background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
        }
        
        .bg-gradient-success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
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
            
            if (!comentario) {
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
        // FUNÇÕES PADRONIZADAS
        // ===================================
        
        /**
         * Função padronizada para redirecionamento para visualização de PPP
         * Centralizada conforme diretrizes de padronização
         */
        function redirecionarParaPpp(pppId) {
            if (!pppId) {
                console.error('❌ ID do PPP não fornecido para redirecionamento');
                return false;
            }
            
            console.log('🔗 Redirecionando para PPP:', pppId);
            window.location.href = '{{ route("ppp.show", ":id") }}'.replace(':id', pppId);
        }
        
        /**
         * Função padronizada para inicializar clique nas linhas da tabela PPP
         */
        function inicializarCliqueTabelaPpp() {
            $('.ppp-row').off('click.ppp-redirect').on('click.ppp-redirect', function() {
                // Verificar se tabela está desabilitada
                if ($('#tabelaPpps').hasClass('tabela-desabilitada')) {
                    return false;
                }
                
                var pppId = $(this).data('ppp-id');
                redirecionarParaPpp(pppId);
            });
        }
        
        /**
         * Função padronizada para exibir alertas do sistema
         * Centralizada conforme diretrizes de padronização
         */
        function mostrarAlerta(mensagem, tipo = 'info') {
            if (!mensagem) {
                console.error('❌ Mensagem não fornecida para o alerta');
                return;
            }
            
            // Remover alertas existentes
            $('.alert-dynamic').remove();
            
            // Mapear tipos de alerta
            const tiposValidos = {
                'success': { classe: 'alert-success', icone: 'fas fa-check-circle' },
                'danger': { classe: 'alert-danger', icone: 'fas fa-exclamation-circle' },
                'warning': { classe: 'alert-warning', icone: 'fas fa-exclamation-triangle' },
                'info': { classe: 'alert-info', icone: 'fas fa-info-circle' }
            };
            
            const tipoConfig = tiposValidos[tipo] || tiposValidos['info'];
            
            // Criar HTML do alerta
            const alertaHtml = `
                <div class="alert ${tipoConfig.classe} alert-dismissible fade show alert-dynamic" role="alert">
                    <i class="${tipoConfig.icone} mr-2"></i>${mensagem}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            
            // Inserir no início do container
            $('.container-fluid').prepend(alertaHtml);
            
            // Auto-hide após 5 segundos (exceto info)
            if (tipo !== 'info') {
                setTimeout(function() {
                    $('.alert-dynamic').fadeOut('slow');
                }, 5000);
            }
            
            console.log(`📢 Alerta exibido [${tipo}]:`, mensagem);
        }
        
        /**
         * Função padronizada para auto-hide de alertas
         */
        function inicializarAutoHideAlertas() {
            setTimeout(function() {
                $('.alert-success, .alert-danger, .alert-warning').not('.alert-info').fadeOut('slow');
            }, 5000);
        }
        
        // ===================================
        // INICIALIZAÇÃO
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
            
            // Auto-hide alerts padronizado
            inicializarAutoHideAlertas();
            
            // Inicializar clique padronizado nas linhas da tabela
            inicializarCliqueTabelaPpp();
            
            // Log de inicialização completa
            console.log('✅ Inicialização da lista PPP concluída');
        });
    </script>
    @yield('extra-js')
@stop