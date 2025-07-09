import $ from 'jquery';
import { HtmlTemplates } from './html-templates.js';
import { ApiService } from './api-service.js';
import { NotificationService } from './notification-service.js';

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

    init: function() {
        this.setupProgressIndicator();
        this.setupCardBehavior();
        this.hideActionButtons();
        this.showCurrentStep();
        this.bindEvents();
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
                    const saved = await this.savePartialPpp();
                    if (saved) {
                        this.goToStep(currentStep + 1);
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
    },

    async savePartialPpp() {
        try {
            const formData = this.collectFormData();
            const result = await ApiService.savePartialPpp(formData);
            
            this.pppId = result.ppp_id;
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
        formData.append('estimativa_valor', 'R$ 1.000,00');
        formData.append('justificativa_valor', 'Valor a ser definido nas próximas etapas');
        formData.append('origem_recurso', 'A definir');
        formData.append('vinculacao_item', 'Não');
        formData.append('tem_contrato_vigente', 'Não');

        return formData;
    },

    updateFormForEdit: function() {
        $('form').attr('action', `/ppp/${this.pppId}`);
        $('form').append('<input type="hidden" name="_method" value="PUT">');
    },

    validateCurrentStep: function(step) {
        if (step === 1) {
            const requiredFields = $('#card-informacoes-item').find('input[required], select[required], textarea[required]');
            
            let isValid = true;
            requiredFields.each(function() {
                if (!$(this).val().trim()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            return isValid;
        }
        
        const cardConfig = this.cards.find(c => c.step === step);
        if (!cardConfig) return true;

        const card = $(`#${cardConfig.id}`);
        const requiredFields = card.find('input[required], select[required], textarea[required]');
        
        let isValid = true;
        requiredFields.each(function() {
            if (!$(this).prop('disabled') && !$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            }
        });

        return isValid;
    },

    showValidationAlert: function(step) {
        const cardConfig = this.cards.find(c => c.step === step);
        alert(`Por favor, preencha todos os campos obrigatórios em "${cardConfig.title}" antes de continuar.`);
    },

    goToStep: function(step) {
        if (step < 1 || step > this.totalSteps) return;

        this.hideCurrentStep();

        // Aumentar o timeout para garantir que a transição seja completa
        setTimeout(() => {
            this.showStep(step);
            this.currentStep = step;
            this.updateProgressIndicator();
            
            $('html, body').animate({
                scrollTop: $('.card').first().offset().top - 100
            }, 500);
        }, 500); // Aumentado de 300ms para 500ms
    },

    showCurrentStep: function() {
        // Mostrar apenas o card da etapa atual (inicialmente etapa 1 - card azul)
        this.showStep(this.currentStep);
    },

    showStep: function(step) {
        if (step === 1) {
            $('#card-informacoes-item').show();
        } else if (step === 2) {
            // Solução mais robusta para o card amarelo
            const cardAmarelo = $('#card-contrato-vigente');
            if (cardAmarelo.length === 0) {
                console.error('Card amarelo não encontrado!');
                return;
            }
            
            const cardAmareloContainer = cardAmarelo.closest('.col-lg-6, .col-md-6, .col-12');
            cardAmareloContainer.removeClass('col-lg-6 col-md-6').addClass('col-12');
            
            // Garantir que o card está visível antes de aplicar fadeIn
            cardAmarelo.css('display', 'block').hide().fadeIn(300);
        } else if (step === 3) {
            const cardVerde = this.findCardByContent('Informações Financeiras');
            const cardCiano = this.findCardByContent('Vinculação/Dependência');
            
            const cardVerdeContainer = cardVerde.closest('.col-lg-6, .col-12');
            cardVerdeContainer.removeClass('col-lg-6 col-12').addClass('col-md-6');
            
            const cardCianoContainer = cardCiano.closest('.col-12');
            cardCianoContainer.removeClass('col-12').addClass('col-md-6');
            
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
    }
};