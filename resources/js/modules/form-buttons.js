import $ from 'jquery';
import { FormValidation } from './form-validation.js';
import { ApiService } from './api-service.js';

/**
 * Módulo de Botões do Formulário
 * Gerencia comportamento dos botões e submissão
 */
export const FormButtons = {
    isSubmitting: false, // Adicionar flag de controle
    init: function() {
        console.log('🚀 FormButtons.init() - Inicializando módulo de botões');
        this.bindButtonEvents();
        this.bindFormSubmission();
        
        // Configurar token CSRF para todas as requisições AJAX
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        console.log('🔑 CSRF Token encontrado:', csrfToken ? 'SIM' : 'NÃO');
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
    },

    bindButtonEvents: function() {
        console.log('🔗 FormButtons.bindButtonEvents() - Vinculando eventos dos botões');
        $('#btn-salvar-rascunho').on('click', (e) => {
            console.log('💾 Botão Salvar Rascunho clicado');
            this.handleSaveDraft($(e.target));
        });
    },

    bindFormSubmission: function() {
        console.log('📝 FormButtons.bindFormSubmission() - Vinculando submissão do formulário');
        $('form').on('submit', async (e) => {
            console.log('📤 Formulário submetido - preventDefault aplicado');
            e.preventDefault();
            return await this.handleSubmit(e, $(e.target));
        });
    },

    handleSaveDraft: function(btn) {
        console.log('💾 FormButtons.handleSaveDraft() - Iniciando salvamento de rascunho');
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Salvando...');
        
        setTimeout(function() {
            console.log('✅ Simulação de salvamento de rascunho concluída');
            btn.prop('disabled', false).html('<i class="fas fa-check me-2"></i>Salvo!');
            
            setTimeout(function() {
                btn.html(originalText);
                console.log('🔄 Botão restaurado ao estado original');
            }, 2000);
        }, 1000);
    },

    async handleSubmit(e, form) {
        console.log('🚀 FormButtons.handleSubmit() - Iniciando processamento do formulário');
        
        // Verificar se já está processando
        if (this.isSubmitting) {
            console.log('⚠️ Submissão já em andamento, ignorando clique duplo');
            return false;
        }
        
        this.isSubmitting = true; // Marcar como processando
        
        const submitBtn = form.find('button[type="submit"]');
        const isEdit = form.find('input[name="_method"][value="PUT"]').length > 0;
        
        console.log('📊 Dados do formulário:', {
            'form_action': form.attr('action'),
            'is_edit': isEdit,
            'form_data_length': form.serialize().length
        });
        
        const camposVazios = FormValidation.validateForm();
        
        if (camposVazios.length > 0) {
            console.error('❌ Validação falhou - Campos obrigatórios vazios:', camposVazios);
            alert('Campos obrigatórios não preenchidos: ' + camposVazios.join(', '));
            return false;
        }
        
        console.log('✅ Validação passou - Todos os campos obrigatórios preenchidos');
        
        submitBtn.prop('disabled', true)
               .html('<i class="fas fa-spinner fa-spin me-2"></i>Enviando para aprovação...');
        
        try {
            const formData = new FormData(form[0]);
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            
            console.log('🔑 Token CSRF para requisição:', csrfToken ? 'PRESENTE' : 'AUSENTE');
            
            if (isEdit) {
                console.log('✏️ Modo EDIÇÃO detectado - Enviando para aprovação diretamente');
                
                const pppId = form.attr('action').split('/').pop();
                console.log('🆔 PPP ID extraído:', pppId);
                
                const url = `/ppp/${pppId}/enviar-aprovacao`;
                console.log('🌐 URL da requisição:', url);
                
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                console.log('📡 Resposta da requisição de envio:', {
                    'status': response.status,
                    'ok': response.ok,
                    'status_text': response.statusText
                });
                
                if (response.ok) {
                    const result = await response.json();
                    console.log('✅ Resposta JSON recebida:', result);
                    
                    if (result.success) {
                        console.log('🎉 Envio para aprovação bem-sucedido - Redirecionando');
                        window.location.href = '/ppp';
                    } else {
                        console.error('❌ Erro no resultado:', result.message);
                        throw new Error(result.message || 'Erro ao enviar para aprovação');
                    }
                } else {
                    console.error('❌ Erro HTTP na requisição:', response.status);
                    const errorData = await response.json().catch(() => ({ message: 'Erro desconhecido' }));
                    console.error('❌ Dados do erro:', errorData);
                    throw new Error(errorData.message || 'Erro ao enviar para aprovação');
                }
            } else {
                console.log('🆕 Modo CRIAÇÃO detectado - Salvando e enviando para aprovação');
                
                // ✅ CORREÇÃO: Enviar 'SIM' em vez de '1'
                formData.append('enviar_aprovacao', 'SIM');
                console.log('📝 Parâmetro enviar_aprovacao=SIM adicionado ao FormData');
                
                const createUrl = '/ppp';
                console.log('🌐 URL de criação:', createUrl);
                
                const response = await fetch(createUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                console.log('📡 Resposta da criação do PPP:', {
                    'status': response.status,
                    'ok': response.ok,
                    'status_text': response.statusText
                });
                
                if (response.ok) {
                    const result = await response.json();
                    console.log('✅ PPP criado e processado:', result);
                    
                    if (result.success) {
                        console.log('🎉 Processo completo bem-sucedido - Redirecionando');
                        console.log('📊 Status final do PPP:', result.status_id);
                        window.location.href = '/ppp';
                    } else {
                        console.error('❌ Erro no resultado:', result.message);
                        throw new Error(result.message || 'Erro ao processar PPP');
                    }
                } else {
                    console.error('❌ Erro HTTP na criação do PPP:', response.status);
                    const errorData = await response.json().catch(() => ({ message: 'Erro desconhecido' }));
                    console.error('❌ Dados do erro na criação:', errorData);
                    throw new Error(errorData.message || 'Erro ao salvar PPP');
                }
            }
        } catch (error) {
            console.error('💥 Erro capturado no handleSubmit:', error);
            console.error('💥 Stack trace:', error.stack);
            alert('Erro ao processar solicitação: ' + error.message);

            submitBtn.prop('disabled', false)
                   .html('<i class="fas fa-paper-plane me-2"></i>Salvar e Enviar para Aprovação');
        } finally {
            this.isSubmitting = false; // Resetar flag independente do resultado
        }

        console.log('🏁 FormButtons.handleSubmit() - Finalizando processamento');
        return false;
    },

    /**
     * Função centralizada para carregar histórico do PPP
     * @param {number} pppId - ID do PPP
     * @param {string} nomeItem - Nome do item PPP
     */
    carregarHistoricoPPP: function(pppId, nomeItem) {
        // Verificar se o modal já existe, se não, criar
        if (!$('#historicoModal').length) {
            const modalHtml = `
                <div class="modal fade" id="historicoModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title" id="historicoModalTitle">
                                    <i class="fas fa-history mr-2"></i>Histórico do PPP
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span>&times;</span>
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
            `;
            $('body').append(modalHtml);
        }

        // Atualizar título e abrir modal
        $('#historicoModalTitle').html(`<i class="fas fa-history mr-2"></i>Histórico: ${nomeItem}`);
        $('#historicoModalBody').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin mr-2"></i>Carregando histórico...</div>');
        $('#historicoModal').modal('show');

        // Usar apenas jQuery modal (Bootstrap 4)
        $('#historicoModal').modal('show');

        // Requisição AJAX
        $.ajax({
            url: `/ppp/${pppId}/historico`,
            type: 'GET',
            success: function(response) {
                $('#historicoModalBody').html(response);
            },
            error: function(xhr, status, error) {
                $('#historicoModalBody').html(
                    '<div class="alert alert-danger">' +
                    '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                    'Erro ao carregar histórico. Tente novamente.' +
                    '</div>'
                );
                console.error('Erro ao carregar histórico:', error);
            }
        });
    },

    // Função de compatibilidade (alias)
    carregarHistorico: function(pppId, nomeItem) {
        return this.carregarHistoricoPPP(pppId, nomeItem);
    }

};

// Expor funções globalmente para compatibilidade com inline onclick
window.carregarHistoricoPPP = FormButtons.carregarHistoricoPPP.bind(FormButtons);
window.carregarHistorico = FormButtons.carregarHistorico.bind(FormButtons);
