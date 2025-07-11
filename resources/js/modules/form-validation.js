import $ from 'jquery';

/**
 * Módulo de Validação de Formulário
 * Gerencia validação em tempo real e tooltips
 */
export const FormValidation = {
    init: function() {
        this.bindValidationEvents();
        this.setupCustomValidation();
    },

    bindValidationEvents: function() {
        // Validação em tempo real para campos obrigatórios (apenas no blur, não no show)
        $(document).on('blur', 'input[required], select[required], textarea[required]', function() {
            // Só validar se o campo já foi tocado pelo usuário
            if ($(this).data('user-interacted')) {
                FormValidation.validateField($(this));
            }
        });

        // Marcar campo como interagido pelo usuário
        $(document).on('focus input change', 'input[required], select[required], textarea[required]', function() {
            $(this).data('user-interacted', true);
        });

        // Limpar validação ao focar
        $(document).on('focus', 'input[required], select[required], textarea[required]', function() {
            FormValidation.clearValidation($(this));
        });

        // Validação para campos monetários
        $(document).on('blur', '.estimativa_valor, .valor_contrato_atualizado', function() {
            FormValidation.validateMonetaryField($(this));
        });
    },

    setupCustomValidation: function() {
        // Configurar mensagens customizadas
        $('input[required], select[required], textarea[required]').each(function() {
            this.setCustomValidity('');
            $(this).attr('title', 'Preencha este campo');
        });
    },

    validateField: function(field) {
        const value = field.val();
        const fieldName = field.attr('name');
        
        // Validação específica para select de origem_recurso
        if (fieldName === 'origem_recurso') {
            const isEmpty = !value || value.trim() === '' || value === 'A definir';
            
            if (isEmpty && field.is(':visible') && !field.prop('disabled')) {
                this.markFieldAsInvalid(field, 'Selecione uma origem do recurso');
                return false;
            } else {
                this.markFieldAsValid(field);
                return true;
            }
        }
        
        // Validação específica para valor_contrato_atualizado
        if (fieldName === 'valor_contrato_atualizado') {
            const isEmpty = !value || value.trim() === '';
            
            if (isEmpty && field.is(':visible') && !field.prop('disabled')) {
                this.markFieldAsInvalid(field, 'Informe o valor para +1 exercício');
                return false;
            } else {
                this.markFieldAsValid(field);
                return true;
            }
        }
        
        // Validação padrão para outros campos
        const isEmpty = !value || value.trim() === '';
        
        if (isEmpty && field.is(':visible') && !field.prop('disabled')) {
            this.markFieldAsInvalid(field, 'Este campo é obrigatório');
            return false;
        } else {
            this.markFieldAsValid(field);
            return true;
        }
    },

    validateMonetaryField: function(field) {
        const value = field.val();
        const monetaryPattern = /^R\$\s?\d{1,3}(\.\d{3})*(,\d{2})?$/;
        
        if (value && !monetaryPattern.test(value)) {
            this.markFieldAsInvalid(field, 'Formato inválido. Use: R$ 0,00');
            return false;
        } else if (value) {
            this.markFieldAsValid(field);
            return true;
        }
        return true;
    },

    validateVisibleRequiredFields: function() {
        let isValid = true;
        const visibleRequiredFields = $('input[required]:visible, select[required]:visible, textarea[required]:visible')
            .not(':disabled');
        
        visibleRequiredFields.each(function() {
            if (!FormValidation.validateField($(this))) {
                isValid = false;
            }
        });
        
        return isValid;
    },

    showFirstInvalidFieldTooltip: function() {
        const firstInvalidField = $('.is-invalid:visible').first();
        if (firstInvalidField.length) {
            // Focar no primeiro campo inválido
            firstInvalidField.focus();
            
            // Mostrar tooltip nativo do HTML5
            if (firstInvalidField[0].reportValidity) {
                const fieldName = firstInvalidField.attr('name');
                let message = 'Preencha este campo';
                
                if (fieldName === 'origem_recurso') {
                    message = 'Selecione uma origem do recurso';
                }
                
                firstInvalidField[0].setCustomValidity(message);
                firstInvalidField[0].reportValidity();
                firstInvalidField[0].setCustomValidity(''); // Limpar após mostrar
            }
            
            // Scroll suave até o campo
            $('html, body').animate({
                scrollTop: firstInvalidField.offset().top - 100
            }, 300);
        }
    },

    markFieldAsInvalid: function(field, message) {
        field.removeClass('is-valid').addClass('is-invalid');
        
        // Remover mensagem anterior
        field.siblings('.invalid-feedback').remove();
        
        // Adicionar nova mensagem
        field.after(`<div class="invalid-feedback">${message}</div>`);
        
        // Configurar tooltip nativo
        field[0].setCustomValidity(message);
    },

    markFieldAsValid: function(field) {
        field.removeClass('is-invalid').addClass('is-valid');
        field.siblings('.invalid-feedback').remove();
        field[0].setCustomValidity('');
    },

    clearValidation: function(field) {
        field.removeClass('is-invalid is-valid');
        field.siblings('.invalid-feedback').remove();
        field[0].setCustomValidity('');
    },

    validateForm: function() {
        const requiredFields = $('input[required]:visible, select[required]:visible, textarea[required]:visible')
            .not(':disabled');
        const emptyFields = [];
        
        requiredFields.each(function() {
            const field = $(this);
            if (!FormValidation.validateField(field)) {
                emptyFields.push(field.attr('name') || field.attr('id'));
            }
        });
        
        return emptyFields;
    }
};