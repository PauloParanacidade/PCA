@extends('layouts.adminlte-custom')

@section('title', 'PPPs para Avaliar')

@section('content_header')
    @parent
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-check mr-2"></i>PPPs para Avaliar</h1>
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
    <div class="card shadow-sm">
        <div class="card-header bg-gradient-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list mr-2"></i>Listagem de PPPs
                <small class="ml-2">({{ $ppps->total() }} {{ $ppps->total() == 1 ? 'item' : 'itens' }})</small>
            </h5>
        </div>
        <div class="card-body p-0">
            @if($ppps->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light text-center">
                            <tr>
                                <th width="2%">#</th>
                                <th width="32%">Nome do Item</th>
                                <th width="12%">Prioridade</th>
                                <th width="12%">Área Solicitante</th>
                                <th width="15%">Responsável Anterior</th>
                                <th width="15%">Status</th>
                                <th width="12%">Valor Estimado</th>
                                <th width="18%" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ppps as $ppp)
                                <tr class="ppp-row text-center" data-ppp-id="{{ $ppp->id }}" style="cursor: pointer;">
                                    <td class="align-middle font-weight-bold">{{ $ppp->id }}</td> 
                                    <td class="align-middle">  {{-- Coluna Nome do Item --}}
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-bold">{{ $ppp->nome_item }}</span>
                                            @if($ppp->descricao)
                                                <small class="text-muted">{{ Str::limit($ppp->descricao, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-middle"> {{-- Coluna Prioridade --}}
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
                                    <td class="align-middle"> {{-- Coluna Sigla da Área solicitante --}}
                                        <span class="badge badge-secondary">
                                            {{ $ppp->user->department ?? 'Área N/A' }}
                                        </span>
                                    </td>
                                    <td class="align-middle"> {{-- Coluna Responsável Anterior --}}
                                        <span class="badge badge-info">
                                            {{ $ppp->sender_name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="align-middle"> {{-- Coluna Status --}}
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
                                    <td class="align-middle"> {{-- Coluna Valor estimado --}}
                                        <span class="text-success font-weight-bold">
                                            R$ {{ number_format($ppp->estimativa_valor ?? 0, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center"> {{-- Coluna Ações --}}
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('ppp.show', $ppp->id) }}" class="btn btn-sm btn-outline-info" title="Visualizar" onclick="event.stopPropagation();">
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
                
                <!-- Paginação -->
                @if($ppps->hasPages())
                    <div class="card-footer">
                        {{ $ppps->links('custom.pagination') }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum PPP encontrado</h5>
                    <p class="text-muted mb-4">Você ainda não tem nenhum PPP para avaliar.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Confirmação para Iniciar Reunião DIREX -->
<div class="modal fade" id="modalConfirmarDirectx" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarDirectxLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalConfirmarDirectxLabel">
                    <i class="fas fa-users mr-2"></i>Confirmar Início da Reunião DIREX
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Atenção:</strong> Já ordenou os PPPs no modo desejado? (por prioridade, Valor Estimado, etc.)
                </div>
                <p>Se prosseguir, a reunião da DIREX irá seguir a sequência atual, como está. Se desejar reordenar, clique em <strong>Voltar</strong>.</p>
                <p class="text-danger"><strong>Importante:</strong> Esse ordenamento não poderá ser mais alterado após o início da reunião na DIREX.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                </button>
                <button type="button" class="btn btn-primary" onclick="confirmarInicioReuniaoDirectx()">
                    <i class="fas fa-play mr-1"></i>Prosseguir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Aprovação do Conselho -->
<div class="modal fade" id="modalConselho" tabindex="-1" role="dialog" aria-labelledby="modalConselhoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalConselhoLabel">
                    <i class="fas fa-gavel mr-2"></i>Aprovação do Conselho
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="fas fa-question-circle fa-3x text-warning mb-3"></i>
                    <h5>Conselho aprovou o PCA do Paranacidade?</h5>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                </button>
                <button type="button" class="btn btn-success mr-2" onclick="processarDecisaoConselho(true)">
                    <i class="fas fa-check mr-1"></i>Sim
                </button>
                <button type="button" class="btn btn-danger" onclick="processarDecisaoConselho(false)">
                    <i class="fas fa-times mr-1"></i>Não
                </button>
            </div>
        </div>
    </div>
</div>

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
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Tem certeza que deseja excluir definitivamente o PPP "<span id="nomeItemConfirmacaoFinal"></span>"?</strong>
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
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i>Confirmar Exclusão Definitiva
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
    
    /* CSS para timeline do histórico */
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
    
    /* Estilos específicos para botões da secretária */
    .btn-lg {
        padding: 12px 20px;
        font-size: 1.1rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-lg:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .btn-lg:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
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
@endsection

@section('js')
    @vite('resources/js/ppp-form.js')
    <script>
        // ===================================
        // VARIÁVEIS GLOBAIS
        // ===================================
        let pppParaExcluir = {
            id: null,
            nome: null
        };
        
        let estadoReuniao = {
            ativa: false,
            pppAtual: null,
            excelGerado: false,
            pdfGerado: false
        };

        // ===================================
        // FUNÇÕES DA SECRETÁRIA - DIREX E CONSELHO
        // ===================================
        
        /**
         * Iniciar ou retomar reunião DIREX
         */
        function iniciarOuRetomarReuniaoDirectx() {
            console.log('🎯 Iniciando/retomando reunião DIREX');
            
            // Verificar se há reunião ativa
            $.ajax({
                url: '{{ route("ppp.direx.verificar-reuniao-ativa") }}',
                type: 'GET',
                success: function(response) {
                    if (response.reuniao_ativa) {
                        // Retomar reunião
                        retomarReuniaoDirectx(response.ppp_atual);
                    } else {
                        // Iniciar nova reunião
                        $('#modalConfirmarDirectx').modal('show');
                    }
                },
                error: function(xhr) {
                    console.error('Erro ao verificar reunião:', xhr);
                    mostrarAlerta('Erro ao verificar status da reunião.', 'danger');
                }
            });
        }
        
        /**
         * Confirmar início da reunião DIREX
         */
        function confirmarInicioReuniaoDirectx() {
            $('#modalConfirmarDirectx').modal('hide');
            
            $.ajax({
                url: '{{ route("ppp.direx.iniciar-reuniao") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta(response.message, 'success');
                        atualizarEstadoReuniao(true);
                        
                        // Redirecionar para o primeiro PPP
                        if (response.primeiro_ppp_id) {
                            window.location.href = `{{ route('ppp.show', ':id') }}`.replace(':id', response.primeiro_ppp_id);
                        }
                    } else {
                        mostrarAlerta(response.message, 'warning');
                    }
                },
                error: function(xhr) {
                    console.error('Erro ao iniciar reunião:', xhr);
                    mostrarAlerta('Erro ao iniciar reunião da DIREX.', 'danger');
                }
            });
        }
        
        /**
         * Retomar reunião DIREX
         */
        function retomarReuniaoDirectx(pppAtual) {
            console.log('🔄 Retomando reunião DIREX no PPP:', pppAtual);
            atualizarEstadoReuniao(true, pppAtual);
            
            if (pppAtual) {
                window.location.href = `{{ route('ppp.show', ':id') }}`.replace(':id', pppAtual);
            } else {
                mostrarAlerta('Reunião retomada. Navegue pelos PPPs usando os botões de navegação.', 'info');
            }
        }
        
        /**
         * Gerar relatório Excel
         */
        function gerarRelatorioExcel() {
            console.log('📊 Gerando relatório Excel');
            
            $.ajax({
                url: '{{ route("ppp.relatorios.gerar-excel") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta(response.message, 'success');
                        estadoReuniao.excelGerado = true;
                        atualizarBotoesSecretaria();
                        
                        // Download do arquivo se fornecido
                        if (response.download_url) {
                            window.open(response.download_url, '_blank');
                        }
                    } else {
                        mostrarAlerta(response.message, 'warning');
                    }
                },
                error: function(xhr) {
                    console.error('Erro ao gerar Excel:', xhr);
                    mostrarAlerta('Erro ao gerar relatório Excel.', 'danger');
                }
            });
        }
        
        /**
         * Gerar relatório PDF
         */
        function gerarRelatorioPdf() {
            console.log('📄 Gerando relatório PDF');
            
            $.ajax({
                url: '{{ route("ppp.relatorios.gerar-pdf") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta(response.message, 'success');
                        estadoReuniao.pdfGerado = true;
                        atualizarBotoesSecretaria();
                        
                        // Download do arquivo se fornecido
                        if (response.download_url) {
                            window.open(response.download_url, '_blank');
                        }
                    } else {
                        mostrarAlerta(response.message, 'warning');
                    }
                },
                error: function(xhr) {
                    console.error('Erro ao gerar PDF:', xhr);
                    mostrarAlerta('Erro ao gerar relatório PDF.', 'danger');
                }
            });
        }
        
        /**
         * Abrir modal de aprovação do Conselho
         */
        function abrirModalConselho() {
            $('#modalConselho').modal('show');
        }
        
        /**
         * Processar decisão do Conselho
         */
        function processarDecisaoConselho(aprovado) {
            $('#modalConselho').modal('hide');
            
            const acao = aprovado ? 'aprovado' : 'reprovado';
            console.log(`🏛️ Processando decisão do Conselho: ${acao}`);
            
            $.ajax({
                url: '{{ route("ppp.conselho.processar") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    aprovado: aprovado
                },
                success: function(response) {
                    if (response.success) {
                        const tipoAlerta = aprovado ? 'success' : 'warning';
                        mostrarAlerta(response.message, tipoAlerta);
                        
                        // Desabilitar botão Conselho
                        $('#btnConselho').prop('disabled', true);
                        
                        // Atualizar página após 2 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        mostrarAlerta(response.message, 'warning');
                    }
                },
                error: function(xhr) {
                    console.error('Erro ao processar decisão do Conselho:', xhr);
                    mostrarAlerta('Erro ao processar decisão do Conselho.', 'danger');
                }
            });
        }
        
        /**
         * Atualizar estado da reunião
         */
        function atualizarEstadoReuniao(ativa, pppAtual = null) {
            estadoReuniao.ativa = ativa;
            estadoReuniao.pppAtual = pppAtual;
            
            // Atualizar interface
            atualizarBotoesSecretaria();
            atualizarStatusReuniao();
            
            // Desabilitar/habilitar tabela
            if (ativa) {
                $('#tabelaPpps').addClass('tabela-desabilitada');
            } else {
                $('#tabelaPpps').removeClass('tabela-desabilitada');
            }
        }
        
        /**
         * Atualizar botões da secretária
         */
        function atualizarBotoesSecretaria() {
            const btnDirectx = $('#btnDirectx');
            const btnExcel = $('#btnGerarExcel');
            const btnPdf = $('#btnGerarPdf');
            const btnConselho = $('#btnConselho');
            
            if (estadoReuniao.ativa) {
                btnDirectx.html('<i class="fas fa-pause mr-2"></i><strong>DIREX</strong><br><small>Reunião em Andamento</small>');
                btnExcel.prop('disabled', true);
                btnPdf.prop('disabled', true);
                btnConselho.prop('disabled', true);
            } else {
                btnDirectx.html('<i class="fas fa-users mr-2"></i><strong>DIREX</strong><br><small>Iniciar/Retomar Reunião</small>');
                
                // Habilitar botões de relatório se reunião foi encerrada
                const reuniaoEncerrada = !estadoReuniao.ativa && (estadoReuniao.excelGerado || estadoReuniao.pdfGerado);
                btnExcel.prop('disabled', estadoReuniao.excelGerado);
                btnPdf.prop('disabled', estadoReuniao.pdfGerado);
                
                // Habilitar Conselho se ambos relatórios foram gerados
                btnConselho.prop('disabled', !(estadoReuniao.excelGerado && estadoReuniao.pdfGerado));
            }
        }
        
        /**
         * Atualizar status da reunião
         */
        function atualizarStatusReuniao() {
            const alertStatus = $('#alertStatusReuniao');
            const textoStatus = $('#textoStatusReuniao');
            
            if (estadoReuniao.ativa) {
                textoStatus.text('Reunião DIREX em andamento. Tabela desabilitada para navegação individual.');
                alertStatus.removeClass('alert-info alert-success').addClass('alert-warning').show();
            } else if (estadoReuniao.excelGerado || estadoReuniao.pdfGerado) {
                textoStatus.text('Reunião DIREX encerrada. Relatórios disponíveis para geração.');
                alertStatus.removeClass('alert-warning alert-info').addClass('alert-success').show();
            } else {
                alertStatus.hide();
            }
        }
        
        /**
         * Mostrar alerta
         */
        function mostrarAlerta(mensagem, tipo = 'info') {
            const alertClass = `alert-${tipo}`;
            const iconClass = {
                'success': 'fas fa-check-circle',
                'danger': 'fas fa-exclamation-circle',
                'warning': 'fas fa-exclamation-triangle',
                'info': 'fas fa-info-circle'
            }[tipo] || 'fas fa-info-circle';
            
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="${iconClass} mr-2"></i>${mensagem}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            
            // Remover alertas existentes e adicionar novo
            $('.container-fluid .alert').remove();
            $('.container-fluid').prepend(alertHtml);
            
            // Auto-hide após 5 segundos
            setTimeout(() => {
                $('.alert').fadeOut('slow');
            }, 5000);
        }

        // ===================================
        // FUNÇÕES ORIGINAIS DO SISTEMA
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
        // INICIALIZAÇÃO
        // ===================================
        
        $(document).ready(function() {
            console.log('🚀 === INICIALIZAÇÃO DA PÁGINA INDEX ===');
            
            // Debug: Verificar se elementos existem
            console.log('🔍 Verificações iniciais:');
            console.log('- Modal histórico existe:', $('#historicoModal').length > 0);
            console.log('- FormButtons existe:', typeof FormButtons !== 'undefined');
            console.log('- jQuery existe:', typeof $ !== 'undefined');
            console.log('- Bootstrap modal existe:', typeof $.fn.modal !== 'undefined');
            
            // Verificar se há PPPs na tabela
            const totalPpps = $('.ppp-row').length;
            console.log('- Total de PPPs na tabela:', totalPpps);
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Clique em qualquer parte da linha do PPP para visualizar
            $('.ppp-row').click(function() {
                // Verificar se tabela está desabilitada
                if ($('#tabelaPpps').hasClass('tabela-desabilitada')) {
                    return false;
                }
                
                var pppId = $(this).data('ppp-id');
                console.log('🔗 Redirecionando para PPP:', pppId);
                window.location.href = '{{ route("ppp.show", ":id") }}'.replace(':id', pppId);
            });
            
            // Inicializar estado da secretária se aplicável
            @if(Auth::user()->hasRole('secretaria'))
                console.log('👩‍💼 Usuário é secretária - inicializando controles especiais');
                
                // Verificar estado inicial da reunião
                $.ajax({
                    url: '{{ route("ppp.direx.verificar-reuniao-ativa") }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.reuniao_ativa) {
                            atualizarEstadoReuniao(true, response.ppp_atual);
                        }
                        
                        // Verificar se relatórios já foram gerados
                        estadoReuniao.excelGerado = response.excel_gerado || false;
                        estadoReuniao.pdfGerado = response.pdf_gerado || false;
                        atualizarBotoesSecretaria();
                    },
                    error: function(xhr) {
                        console.warn('Não foi possível verificar estado inicial da reunião');
                    }
                });
            @endif
            
            // Log de inicialização completa
            console.log('✅ Inicialização da página concluída');
        });
    </script>
@endsection