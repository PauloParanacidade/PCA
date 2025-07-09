import $ from 'jquery';
import { FormValidation } from './form-validation.js';
import { ApiService } from './api-service.js';

/**
 * Módulo de Botões do Formulário
 * Gerencia comportamento dos botões e submissão
 */
export const FormButtons = {
    init: function() {
        this.bindButtonEvents();
        this.bindFormSubmission();
    },

    bindButtonEvents: function() {
        $('#btn-salvar-rascunho').on('click', (e) => {
            this.handleSaveDraft($(e.target));
        });
    },

    bindFormSubmission: function() {
        $('form').on('submit', async (e) => {
            e.preventDefault();
            return await this.handleSubmit(e, $(e.target));
        });
    },

    handleSaveDraft: function(btn) {
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Salvando...');
        
        setTimeout(function() {
            btn.prop('disabled', false).html('<i class="fas fa-check me-2"></i>Salvo!');
            
            setTimeout(function() {
                btn.html(originalText);
            }, 2000);
        }, 1000);
    },

    async handleSubmit(e, form) {
        const submitBtn = form.find('button[type="submit"]');
        const isEdit = form.find('input[name="_method"][value="PUT"]').length > 0;
        
        console.log('Dados do formulário:', form.serialize());
        
        const camposVazios = FormValidation.validateForm();
        
        if (camposVazios.length > 0) {
            console.log('Campos obrigatórios vazios:', camposVazios);
            alert('Campos obrigatórios não preenchidos: ' + camposVazios.join(', '));
            return false;
        }
        
        submitBtn.prop('disabled', true)
               .html('<i class="fas fa-spinner fa-spin me-2"></i>Enviando para aprovação...');
        
        try {
            const formData = new FormData(form[0]);
            
            if (isEdit) {
                // Se é edição, usar a rota de enviar para aprovação
                const pppId = form.attr('action').split('/').pop();
                const response = await fetch(`/ppp/${pppId}/enviar-aprovacao`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    window.location.href = '/ppp';
                } else {
                    throw new Error('Erro ao enviar para aprovação');
                }
            } else {
                // Se é criação, primeiro salvar e depois enviar
                formData.append('enviar_aprovacao', '1');
                
                const response = await fetch('/ppp', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    // Enviar para aprovação após criação
                    const enviarResponse = await fetch(`/ppp/${result.ppp_id}/enviar-aprovacao`, {
                        method: 'POST',
                        body: new FormData(),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    
                    if (enviarResponse.ok) {
                        window.location.href = '/ppp';
                    } else {
                        throw new Error('Erro ao enviar para aprovação');
                    }
                } else {
                    throw new Error('Erro ao salvar PPP');
                }
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao processar solicitação: ' + error.message);
            submitBtn.prop('disabled', false)
                   .html('<i class="fas fa-paper-plane me-2"></i>Salvar e Enviar para Aprovação');
        }
        
        return false;
    }
};