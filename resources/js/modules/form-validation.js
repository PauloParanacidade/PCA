import $ from 'jquery';

/**
 * Módulo de Validação de Formulário
 * Centraliza todas as validações do formulário PPP
 */
export const FormValidation = {
    init: function() {
        this.bindValidationEvents();
    },

    bindValidationEvents: function() {
        // Validação em tempo real
        $('input[required], select[required], textarea[required]').on('blur', (e) => {
            this.validateField($(e.target));
        });
        
        // Validação específica para campos monetários
        $('.estimativa_valor, .valor_contrato_atualizado').on('blur', (e) => {
            this.validateField($(e.target));
        });

        // Limpar validação ao focar
        $('input, select, textarea').on('focus', (e) => {
            this.clearFieldValidation($(e.target));
        });
    },

    validateField: function(field) {
        const value = field.val().trim();
        
        if (this.isEmpty(value)) {
            this.markFieldAsInvalid(field);
        } else {
            this.markFieldAsValid(field);
        }
    },

    isEmpty: function(value) {
        return value === '' || value === null || value === undefined;
    },

    markFieldAsInvalid: function(field) {
        field.addClass('is-invalid');
        if (!field.next('.invalid-feedback').length) {
            field.after('<div class="invalid-feedback">Este campo é obrigatório.</div>');
        }
    },

    markFieldAsValid: function(field) {
        field.removeClass('is-invalid').next('.invalid-feedback').remove();
        field.addClass('is-valid');
    },

    clearFieldValidation: function(field) {
        field.removeClass('is-invalid is-valid').next('.invalid-feedback').remove();
    },

    validateForm: function() {
        let camposVazios = [];
        $('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).prop('disabled') && !$(this).val().trim()) {
                camposVazios.push($(this).attr('name') || 'campo sem nome');
                $(this).addClass('is-invalid');
            }
        });
        return camposVazios;
    }
};