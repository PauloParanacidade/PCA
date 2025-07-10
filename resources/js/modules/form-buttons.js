import $ from 'jquery';
import { FormValidation } from './form-validation.js';
import { ApiService } from './api-service.js';

/**
 * Módulo de Botões do Formulário
 * Gerencia comportamento dos botões e submissão
 */
export const FormButtons = {
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
                console.log('🆕 Modo CRIAÇÃO detectado - Salvando e depois enviando');
                
                formData.append('enviar_aprovacao', '1');
                console.log('📝 Parâmetro enviar_aprovacao adicionado ao FormData');
                
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
                    console.log('✅ PPP criado com sucesso:', result);
                    
                    if (result.success && result.ppp_id) {
                        console.log('🚀 Iniciando envio para aprovação do PPP criado:', result.ppp_id);
                        
                        // Enviar para aprovação após criação
                        const enviarFormData = new FormData();
                        enviarFormData.append('_token', csrfToken);
                        
                        const enviarUrl = `/ppp/${result.ppp_id}/enviar-aprovacao`;
                        console.log('🌐 URL de envio para aprovação:', enviarUrl);
                        
                        const enviarResponse = await fetch(enviarUrl, {
                            method: 'POST',
                            body: enviarFormData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });
                        
                        console.log('📡 Resposta do envio para aprovação:', {
                            'status': enviarResponse.status,
                            'ok': enviarResponse.ok,
                            'status_text': enviarResponse.statusText
                        });
                        
                        if (enviarResponse.ok) {
                            const enviarResult = await enviarResponse.json();
                            console.log('✅ Resultado do envio para aprovação:', enviarResult);
                            
                            if (enviarResult.success) {
                                console.log('🎉 Processo completo bem-sucedido - Redirecionando');
                                window.location.href = '/ppp';
                            } else {
                                console.error('❌ Erro no envio para aprovação:', enviarResult.message);
                                throw new Error(enviarResult.message || 'Erro ao enviar para aprovação');
                            }
                        } else {
                            console.error('❌ Erro HTTP no envio para aprovação:', enviarResponse.status);
                            const errorData = await enviarResponse.json().catch(() => ({ message: 'Erro desconhecido' }));
                            console.error('❌ Dados do erro no envio:', errorData);
                            throw new Error(errorData.message || 'Erro ao enviar para aprovação');
                        }
                    } else {
                        console.error('❌ Erro na criação do PPP:', result.message);
                        throw new Error(result.message || 'Erro ao salvar PPP');
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
        }
        
        console.log('🏁 FormButtons.handleSubmit() - Finalizando processamento');
        return false;
    }
};