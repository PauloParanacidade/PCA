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
                        <thead class="thead-light text-center">
                            <tr>
                                <th width="1%">#</th>
                                <th width="23%">Nome do Item</th>
                                <th width="10%">Área Solicitante</th>
                                <th width="12%">Área Atual</th>
                                <th width="12%">Área Avaliadora</th>
                                <th width="10%">Valor Estimado</th>
                                <th width="10%">Status</th>
                                <th width="10%">Data Criação</th>
                                <th width="15%" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ppps as $ppp)
                                <tr class="ppp-row text-center" data-ppp-id="{{ $ppp->id }}" style="cursor: pointer;">
                                    <td class="align-middle font-weight-bold">{{ $ppp->id }}</td> 
                                    <td class="align-middle">  {{-- Nome do Item --}}
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-bold">{{ $ppp->nome_item }}</span>
                                            @if($ppp->descricao)
                                                <small class="text-muted">{{ Str::limit($ppp->descricao, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-middle"> {{-- Sigla da Área solicitante --}}
                                        <span class="badge badge-secondary">
                                            {{ $ppp->user->department ?? 'Área N/A' }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-info">
                                            {{ $ppp->current_approver }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-secondary">
                                            {{ $ppp->next_approver }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-left"> {{-- Valor estimado --}}
                                        <span class="text-success font-weight-bold">
                                            R$ {{ number_format($ppp->estimativa_valor ?? 0, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    
                                    <td class="align-middle text-left"> {{-- Status --}}
                                        <span class="badge badge-info">
                                            @if($ppp->status)
                                                <i class="fas fa-info-circle mr-1"></i>{{ $ppp->status->nome }}
                                            @else
                                                <i class="fas fa-info-circle mr-1"></i>Status não definido
                                            @endif
                                        </span>
                                    </td>
                                    <td class="align-middle"> {{-- Data Criação --}}
                                        <small>{{ $ppp->created_at ? $ppp->created_at->format('d/m/Y H:i') : 'N/A' }}</small>
                                    </td>
                                    <td class="align-middle text-center"> {{-- Ações --}}
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('ppp.show', $ppp->id) }}" class="btn btn-sm btn-outline-info" title="Visualizar" onclick="event.stopPropagation();">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('ppp.edit', $ppp->id) }}" class="btn btn-sm btn-outline-warning" title="Editar" onclick="event.stopPropagation();">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-historico" 
                                                onclick="event.stopPropagation(); FormButtons.carregarHistoricoPPP({{ $ppp->id }}, '{{ addslashes($ppp->nome_item) }}')"
                                                title="Histórico">
                                                <i class="fas fa-history"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="event.stopPropagation(); confirmarExclusao({{ $ppp->id }}, '{{ addslashes($ppp->nome_item) }}')" title="Remover">
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
                        {{ $ppps->links('custom.pagination') }}
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
    <!-- CSS para timeline do histórico -->
<style>
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

        // ===================================
        // FUNÇÕES DE DEBUG E TESTE
        // ===================================
        
        /**
         * Função para testar o modal de histórico manualmente
         */
        window.testarModal = function() {
            console.log('🧪 === TESTE MANUAL DO MODAL ===');
            console.log('🔍 Verificando elementos no DOM...');
            
            const modal = $('#historicoModal');
            console.log('Modal existe:', modal.length > 0);
            
            if (modal.length > 0) {
                console.log('✅ Modal encontrado, tentando abrir...');
                modal.modal('show');
                console.log('✅ Comando modal.show() executado');
            } else {
                console.log('❌ Modal não encontrado no DOM');
                console.log('🔧 Tentando criar modal dinamicamente...');
                
                const modalHtml = `
                    <div class="modal fade" id="historicoModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title">Teste do Modal</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Este é um teste do modal de histórico.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('body').append(modalHtml);
                console.log('✅ Modal criado, tentando abrir...');
                $('#historicoModal').modal('show');
            }
        };
        
        /**
         * Função para testar a requisição AJAX diretamente
         */
        window.testarAjax = function(pppId) {
            console.log('🧪 === TESTE DA REQUISIÇÃO AJAX ===');
            const url = `/ppp/${pppId}/historico`;
            console.log('🌐 Testando URL:', url);
            
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    console.log('✅ Requisição AJAX bem-sucedida');
                    console.log('📄 Resposta:', response);
                },
                error: function(xhr, status, error) {
                    console.error('❌ Erro na requisição AJAX:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        error: error,
                        response: xhr.responseText
                    });
                }
            });
        };
        
        /**
         * Função para verificar dependências
         */
        window.verificarDependencias = function() {
            console.log('🧪 === VERIFICAÇÃO DE DEPENDÊNCIAS ===');
            console.log('jQuery disponível:', typeof $ !== 'undefined');
            console.log('Bootstrap modal disponível:', typeof $.fn.modal !== 'undefined');
            console.log('FormButtons disponível:', typeof FormButtons !== 'undefined');
            console.log('Modal no DOM:', $('#historicoModal').length > 0);
            
            if (typeof FormButtons !== 'undefined') {
                console.log('carregarHistoricoPPP disponível:', typeof FormButtons.carregarHistoricoPPP === 'function');
            }
            
            // Verificar se há erros no console
            console.log('🔍 Para verificar erros, abra a aba Console nas DevTools');
        };

        // ===================================
        // FUNÇÕES DO SISTEMA
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

        function prosseguirParaConfirmacao() {
            const comentario = document.getElementById('comentarioExclusao').value.trim();
            
            if (!comentario) {
                document.getElementById('comentarioExclusao').classList.add('is-invalid');
                return;
            }
            
            // Fechar primeira modal
            $('#comentarioExclusaoModal').modal('hide');
            
            // Aguardar fechamento e abrir segunda modal
            $('#comentarioExclusaoModal').on('hidden.bs.modal', function() {
                document.getElementById('nomeItemExclusaoFinal').textContent = pppParaExcluir.nome;
                $('#confirmacaoExclusaoModal').modal('show');
                
                // Remover listener para evitar múltiplas execuções
                $(this).off('hidden.bs.modal');
            });
        }

        function excluirPppDefinitivamente() {
            const comentario = document.getElementById('comentarioExclusao').value.trim();
            
            if (!pppParaExcluir.id || !comentario) {
                console.error('Dados insuficientes para exclusão');
                return;
            }
            
            // Criar formulário para envio
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/ppp/${pppParaExcluir.id}`;
            
            // Token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            // Method spoofing para DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            // Comentário
            const comentarioInput = document.createElement('input');
            comentarioInput.type = 'hidden';
            comentarioInput.name = 'comentario_exclusao';
            comentarioInput.value = comentario;
            form.appendChild(comentarioInput);
            
            // Adicionar ao DOM e submeter
            document.body.appendChild(form);
            form.submit();
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
                var pppId = $(this).data('ppp-id');
                console.log('🔗 Redirecionando para PPP:', pppId);
                window.location.href = '{{ route("ppp.show", ":id") }}'.replace(':id', pppId);
            });
            
            // Log de inicialização completa
            console.log('✅ Inicialização da página concluída');
            console.log('💡 Comandos de teste disponíveis:');
            console.log('   - testarModal() - Testa abertura do modal');
            console.log('   - testarAjax(pppId) - Testa requisição AJAX');
            console.log('   - verificarDependencias() - Verifica bibliotecas');
        });
    </script>
@endsection