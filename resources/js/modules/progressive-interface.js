import $ from 'jquery';
import { HtmlTemplates } from './html-templates.js';
import { ApiService } from './api-service.js';
import { NotificationService } from './notification-service.js';
import { FormValidation } from './form-validation.js';
import { ConditionalFields } from './conditional-fields.js';

/**
 * Módulo de Interface Progressiva
 * Gerencia a navegação entre etapas do formulário
 */
export const ProgressiveInterface = {
    currentStep: 1,
    totalSteps: 3,
    cards: [
        { id: 'card-informacoes-item', step: 1, title: 'Informações do Item/Serviço', color: 'primary' },
        { id: 'card-contrato-vigente', step: 2, title: 'Contrato Vigente', color: 'warning' },
        { id: 'cards-finais', step: 3, title: 'Informações Finais', color: 'success' }
    ],
    pppId: null,
    isDraftCreated: false,
    hasUnsavedChanges: false,

    init: function() {
        this.checkExistingDraft();
        this.setupProgressIndicator();
        this.setupCardBehavior();
        this.hideActionButtons();
        this.showCurrentStep();
        this.bindEvents();
        this.trackFormChanges();
    },

    checkExistingDraft: function() {
        // Verificar se estamos editando um PPP existente
        const form = $('form');
        const action = form.attr('action');
        
        if (action && action.includes('/ppp/') && action !== '/ppp') {
            // Extrair ID do PPP da URL
            const matches = action.match(/\/ppp\/(\d+)/);
            if (matches) {
                this.pppId = matches[1];
                this.isDraftCreated = true;
                console.log('PPP existente detectado:', this.pppId);
            }
        }
    },

    trackFormChanges: function() {
        // Monitorar mudanças nos campos do card azul
        $(document).on('input change', '#card-informacoes-item input, #card-informacoes-item select, #card-informacoes-item textarea', () => {
            if (this.isDraftCreated) {
                this.hasUnsavedChanges = true;
            }
        });
    },

    setupProgressIndicator: function() {
        const progressHtml = HtmlTemplates.getProgressIndicator(this.currentStep, this.totalSteps, this.cards);
        $('.card').first().before(progressHtml);
        this.updateProgressIndicator();
    },

    setupCardBehavior: function() {
        this.setupCardIds();
        
        this.cards.forEach(cardConfig => {
            if (cardConfig.step > 1) {
                if (cardConfig.step === 3) {
                    this.findCardByContent('Informações Financeiras').hide();
                    this.findCardByContent('Vinculação/Dependência').hide();
                } else {
                    $(`#${cardConfig.id}`).hide();
                }
            }
        });

        this.addNavigationButtons();
    },

    setupCardIds: function() {
        const cardAzul = this.findCardByContent('Informações do Item');
        if (cardAzul.length && !cardAzul.attr('id')) {
            cardAzul.attr('id', 'card-informacoes-item');
        }

        const cardAmarelo = this.findCardByContent('Contrato Vigente');
        if (cardAmarelo.length && !cardAmarelo.attr('id')) {
            cardAmarelo.attr('id', 'card-contrato-vigente');
        }
    },

    findCardByContent: function(title) {
        return $('.card').filter(function() {
            return $(this).find('.card-header, .card-title, h5, h6').text().includes(title);
        });
    },

    hideActionButtons: function() {
        $('.card').has('.btn[type="submit"], .btn-warning, .btn-secondary').hide();
    },

    addNavigationButtons: function() {
        const cardAzul = $('#card-informacoes-item');
        if (cardAzul.length) {
            const navigationHtml = HtmlTemplates.getNavigationButtons(1, this.totalSteps);
            cardAzul.find('.card-body').append(navigationHtml);
        }

        const cardAmarelo = $('#card-contrato-vigente');
        if (cardAmarelo.length) {
            const navigationHtml = HtmlTemplates.getNavigationButtons(2, this.totalSteps);
            cardAmarelo.find('.card-body').append(navigationHtml);
        }
    },

    bindEvents: function() {
        $(document).on('click', '.btn-next', async (e) => {
            const currentStep = parseInt($(e.target).data('step'));
            
            if (currentStep === 1) {
                if (this.validateCurrentStep(currentStep)) {
                    // Se já existe rascunho, apenas prosseguir
                    if (this.isDraftCreated) {
                        this.goToStep(currentStep + 1);
                    } else {
                        // Criar novo rascunho apenas se não existir
                        const saved = await this.savePartialPpp();
                        if (saved) {
                            this.goToStep(currentStep + 1);
                        }
                    }
                } else {
                    this.showValidationAlert(currentStep);
                }
            } else {
                if (this.validateCurrentStep(currentStep)) {
                    this.goToStep(currentStep + 1);
                } else {
                    this.showValidationAlert(currentStep);
                }
            }
        });

        $(document).on('click', '.btn-prev', (e) => {
            const currentStep = parseInt($(e.target).data('step'));
            this.goToStep(currentStep - 1);
        });

        // Interceptar submissão final para salvar mudanças do card azul
        $(document).on('click', 'button[type="submit"]', async (e) => {
            if (this.hasUnsavedChanges && this.isDraftCreated) {
                e.preventDefault();
                
                try {
                    await this.updateDraftChanges();
                    // Após salvar, submeter o formulário normalmente
                    $(e.target).closest('form')[0].submit();
                } catch (error) {
                    console.error('Erro ao salvar mudanças:', error);
                    alert('Erro ao salvar mudanças. Tente novamente.');
                }
            }
        });
    },

    async updateDraftChanges() {
        try {
            const formData = this.collectAllFormData();
            formData.append('_method', 'PUT');
            
            const response = await fetch(`/ppp/${this.pppId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error('Erro ao atualizar PPP');
            }
            
            this.hasUnsavedChanges = false;
            console.log('Mudanças do card azul salvas com sucesso');
            
        } catch (error) {
            console.error('Erro ao atualizar PPP:', error);
            throw error;
        }
    },

    collectAllFormData: function() {
        const formData = new FormData();
        
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        // Coletar todos os campos do formulário
        $('form input, form select, form textarea').each(function() {
            const field = $(this);
            const name = field.attr('name');
            const value = field.val();
            
            if (name && name !== '_token' && name !== '_method' && value) {
                formData.append(name, value);
            }
        });
        
        return formData;
    },

    async savePartialPpp() {
        try {
            const formData = this.collectFormData();
            const result = await ApiService.savePartialPpp(formData);
            
            this.pppId = result.ppp_id;
            this.isDraftCreated = true;
            this.updateFormForEdit();
            NotificationService.showSuccess('Rascunho do PPP foi salvo com sucesso!');
            
            return true;
        } catch (error) {
            console.error('Erro ao salvar PPP:', error);
            alert('Erro ao salvar. Verifique sua conexão e tente novamente.');
            return false;
        }
    },

    collectFormData: function() {
        const formData = new FormData();
        
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        // Campos do card azul
        const campos = {
            categoria: $('select[name="categoria"]').val(),
            nome_item: $('input[name="nome_item"]').val(),
            descricao: $('textarea[name="descricao"]').val(),
            quantidade: $('input[name="quantidade"]').val(),
            justificativa_pedido: $('textarea[name="justificativa_pedido"]').val(),
            natureza_objeto: $('select[name="natureza_objeto"]').val(),
            grau_prioridade: $('select[name="grau_prioridade"]').val()
        };

        // Validar campos obrigatórios
        const camposVazios = Object.entries(campos).filter(([key, value]) => !value);
        if (camposVazios.length > 0) {
            throw new Error('Campos obrigatórios não preenchidos');
        }

        // Adicionar campos ao FormData
        Object.entries(campos).forEach(([key, value]) => {
            formData.append(key, value);
        });

        // Campos com valores padrão
        formData.append('estimativa_valor', 'R$ 0,01');
        formData.append('justificativa_valor', 'Valor a ser definido nas próximas etapas');
        formData.append('origem_recurso', 'PRC'); // Mudança: usar 'PRC' em vez de 'A definir'
        formData.append('vinculacao_item', 'Não');
        formData.append('tem_contrato_vigente', 'Não');

        return formData;
    },

    updateFormForEdit: function() {
        const form = $('form');
        form.attr('action', `/ppp/${this.pppId}`);
        
        // Adicionar _method apenas se não existir
        if (!form.find('input[name="_method"]').length) {
            form.append('<input type="hidden" name="_method" value="PUT">');
        }
    },

    validateCurrentStep: function(step) {
        if (step === 1) {
            // Validação específica para o card azul usando FormValidation
            const requiredFields = $('#card-informacoes-item').find('input[required], select[required], textarea[required]');
            
            let isValid = true;
            requiredFields.each(function() {
                const field = $(this);
                if (!FormValidation.validateField(field)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                FormValidation.showFirstInvalidFieldTooltip();
            }
            
            return isValid;
        }
        
        if (step === 2) {
            // Validação específica para o card amarelo
            const cardAmarelo = $('#card-contrato-vigente');
            const temContratoVigente = $('#tem_contrato_vigente').val();
            
            let isValid = true;
            
            // Validar se "Objeto tem contrato vigente?" foi respondido
            if (!temContratoVigente) {
                const field = $('#tem_contrato_vigente');
                FormValidation.markFieldAsInvalid(field, 'Preencha este campo');
                isValid = false;
            }
            
            // Se tem contrato vigente = "Sim", validar campos obrigatórios
            if (temContratoVigente === 'Sim') {
                const camposContrato = $('#campos_contrato');
                const requiredFields = camposContrato.find('input[required]:visible, select[required]:visible');
                
                requiredFields.each(function() {
                    const field = $(this);
                    if (!FormValidation.validateField(field)) {
                        isValid = false;
                    }
                });
                
                // Validar se "Prorrogável?" foi respondido
                const contratoProrrogavel = $('#contrato_prorrogavel').val();
                if (!contratoProrrogavel) {
                    const field = $('#contrato_prorrogavel');
                    FormValidation.markFieldAsInvalid(field, 'Preencha este campo');
                    isValid = false;
                }
                
                // Se prorrogável = "Sim", validar campo de renovação
                if (contratoProrrogavel === 'Sim') {
                    const renovContrato = $('#renov_contrato').val();
                    if (!renovContrato) {
                        const field = $('#renov_contrato');
                        FormValidation.markFieldAsInvalid(field, 'Preencha este campo');
                        isValid = false;
                    }
                }
            }
            
            if (!isValid) {
                FormValidation.showFirstInvalidFieldTooltip();
            }
            
            return isValid;
        }
        
        // Validação para outras etapas (steps 3+)
        const cardConfig = this.cards.find(c => c.step === step);
        if (!cardConfig) return true;
        
        const card = $(`#${cardConfig.id}`);
        const requiredFields = card.find('input[required]:visible, select[required]:visible, textarea[required]:visible')
            .not(':disabled');
        
        let isValid = true;
        requiredFields.each(function() {
            // Só validar campos que tiveram interação do usuário
            if ($(this).data('user-interacted') && !FormValidation.validateField($(this))) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            FormValidation.showFirstInvalidFieldTooltip();
        }

        return isValid;
    },

    showStep: function(step) {
        if (step === 1) {
            $('#card-informacoes-item').show();
        } else if (step === 2) {
            // Solução mais robusta para o card amarelo
            const cardAmarelo = $('#card-contrato-vigente');
            if (cardAmarelo.length === 0) {
                console.error('Card amarelo não encontrado!');
                // Tentar recarregar a página se o card não for encontrado
                location.reload();
                return;
            }
            
            // Garantir que o card está completamente visível
            cardAmarelo.css({
                'display': 'block',
                'visibility': 'visible',
                'opacity': '1'
            });
            
            // Reajustar layout
            const cardAmareloContainer = cardAmarelo.closest('.col-lg-6, .col-md-6, .col-12');
            cardAmareloContainer.removeClass('col-lg-6 col-md-6').addClass('col-12');
            
            // Reinicializar campos condicionais
            ConditionalFields.initializeStates();
            
            // Aplicar fadeIn com callback
            cardAmarelo.hide().fadeIn(300, function() {
                // Garantir que todos os elementos estão visíveis
                $(this).find('input, select, textarea').each(function() {
                    $(this).trigger('change');
                });
            });
        } else if (step === 3) {
            const cardVerde = this.findCardByContent('Informações Financeiras');
            const cardCiano = this.findCardByContent('Vinculação/Dependência');
            
            const cardVerdeContainer = cardVerde.closest('.col-lg-6, .col-12');
            cardVerdeContainer.removeClass('col-lg-6 col-12').addClass('col-md-6');
            
            const cardCianoContainer = cardCiano.closest('.col-12');
            cardCianoContainer.removeClass('col-12').addClass('col-md-6');
            
            // Limpar validações anteriores antes de exibir os cards
            cardVerde.find('input, select, textarea').each(function() {
                FormValidation.clearValidation($(this));
            });
            
            cardCiano.find('input, select, textarea').each(function() {
                FormValidation.clearValidation($(this));
            });
            
            cardVerde.fadeIn(300);
            cardCiano.fadeIn(300);
            
            this.showFinalButtons();
        }
    },

    showFinalButtons: function() {
        const finalButtonsHtml = HtmlTemplates.getFinalButtons();
        this.findCardByContent('Vinculação/Dependência').closest('.row').after(finalButtonsHtml);
    },

    updateProgressIndicator: function() {
        const progressPercentage = (this.currentStep / this.totalSteps) * 100;
        
        $('#progress-bar').css('width', `${progressPercentage}%`);
        $('#progress-badge').text(`Etapa ${this.currentStep} de ${this.totalSteps}`);
        
        $('.step-indicator').each((index, element) => {
            const stepNumber = parseInt($(element).data('step'));
            const icon = $(element).find('.step-icon');
            
            if (stepNumber < this.currentStep) {
                icon.removeClass('fa-circle fa-circle-dot').addClass('fa-check-circle text-success');
                $(element).removeClass('text-muted').addClass('text-success');
            } else if (stepNumber === this.currentStep) {
                icon.removeClass('fa-circle fa-check-circle').addClass('fa-circle-dot text-primary');
                $(element).removeClass('text-muted text-success').addClass('text-primary fw-bold');
            } else {
                icon.removeClass('fa-check-circle fa-circle-dot').addClass('fa-circle text-muted');
                $(element).removeClass('text-success text-primary fw-bold').addClass('text-muted');
            }
        });
    },

    hideCurrentStep: function() {
        // Ocultar todos os cards
        $('#card-informacoes-item').hide();
        $('#card-contrato-vigente').hide();
        
        // Ocultar cards da etapa 3
        const cardVerde = this.findCardByContent('Informações Financeiras');
        const cardCiano = this.findCardByContent('Vinculação/Dependência');
        
        if (cardVerde.length) cardVerde.hide();
        if (cardCiano.length) cardCiano.hide();
        
        // Remover botões finais se existirem
        $('.final-buttons').remove();
    },
    // Adicionar após o método hideCurrentStep
    goToStep: function(step) {
        if (step < 1 || step > this.totalSteps) return;
        
        this.hideCurrentStep();
        this.currentStep = step;
        this.showStep(step);
        this.updateProgressIndicator();
    },

    showCurrentStep: function() {
        this.showStep(this.currentStep);
    }
};

