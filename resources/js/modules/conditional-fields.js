import $ from 'jquery';

/**
 * Módulo de Campos Condicionais
 * Gerencia a exibição/ocultação de campos baseado em condições
 */
export const ConditionalFields = {
    init: function() {
        this.bindConditionalEvents();
        this.initializeStates();
    },

    bindConditionalEvents: function() {
        // Vinculação
        $('#vinculacao_item').on('change', (e) => {
            this.toggleVinculacao($(e.target).val() === 'Sim');
        });

        // Contrato vigente - CORRIGIDO
        $('#tem_contrato_vigente').on('change', (e) => {
            this.toggleContratoVigente($(e.target).val());
        });

        // Prorrogação - CORRIGIDO
        $('#contrato_prorrogavel').on('change', (e) => {
            this.toggleProrrogacao($(e.target).val() === 'Sim');
        });
    },

    toggleVinculacao: function(show) {
        const campo = $('#campo_justificativa_vinculacao');
        const textarea = $('#justificativa_vinculacao');
        
        if (show) {
            campo.slideDown(300);
            textarea.attr('required', true);
        } else {
            campo.slideUp(300);
            textarea.removeAttr('required').val('');
        }
    },

    // CORRIGIDO - usar IDs corretos
    toggleContratoVigente: function(valor) {
        const camposContrato = $('#campos-contrato-vigente');
        const campoMesInicio = $('#campo-mes-inicio-prestacao');
        const requiredFields = camposContrato.find('input, select');
        const mesInicioField = $('#mes_inicio_prestacao');
        
        if (valor === 'Sim') {
            camposContrato.slideDown(300);
            campoMesInicio.slideUp(300);
            requiredFields.attr('required', true);
            mesInicioField.removeAttr('required');
        } else if (valor === 'Não') {
            camposContrato.slideUp(300);
            campoMesInicio.slideDown(300);
            requiredFields.removeAttr('required').val('');
            mesInicioField.attr('required', true);
        } else {
            camposContrato.slideUp(300);
            campoMesInicio.slideUp(300);
        }
    },

    // CORRIGIDO - usar ID correto
    toggleProrrogacao: function(show) {
        const campo = $('#campo-pretensao-prorrogacao');
        const select = $('#renov_contrato');
        
        if (show) {
            campo.slideDown(300);
            select.attr('required', true);
        } else {
            campo.slideUp(300);
            select.removeAttr('required').val('');
        }
    },

    initializeStates: function() {
        // Inicializar vinculação
        const vinculacao = $('#vinculacao_item').val();
        if (vinculacao) {
            this.toggleVinculacao(vinculacao === 'Sim');
        }

        // Inicializar contrato vigente
        const contratoVigente = $('#tem_contrato_vigente').val();
        if (contratoVigente) {
            this.toggleContratoVigente(contratoVigente);
        }

        // Inicializar prorrogação
        const prorrogavel = $('#contrato_prorrogavel').val();
        if (prorrogavel) {
            this.toggleProrrogacao(prorrogavel === 'Sim');
        }
    }
};