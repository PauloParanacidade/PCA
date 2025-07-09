import $ from 'jquery';
import 'jquery-maskmoney/dist/jquery.maskMoney.min';

/**
 * Módulo de Máscaras de Formulário
 * Gerencia todas as máscaras de entrada de dados
 */
export const FormMasks = {
    init: function() {
        this.setupMoneyMasks();
        this.setupQuantityMask();
        this.setupTextFormatting();
        this.setupContractMask();
    },

    setupMoneyMasks: function() {
        $('.estimativa_valor, .valor_contrato_atualizado').maskMoney({
            prefix: 'R$ ',
            allowNegative: false,
            thousands: '.',
            decimal: ',',
            affixesStay: true,
            allowZero: true,
            precision: 2
        });

        $('.estimativa_valor, .valor_contrato_atualizado').maskMoney('mask');

        $('.estimativa_valor, .valor_contrato_atualizado').on('keyup', function() {
            $(this).maskMoney('mask');
        });
    },

    setupContractMask: function() {
        $('#num_contrato').mask('0000/0000', {
            placeholder: '____/____',
            translation: {
                '0': {pattern: /[0-9]/}
            }
        });
    },

    setupQuantityMask: function() {
        $('input[name="quantidade"]').on('input', function() {
            let value = $(this).val();
            value = value.replace(/[^a-zA-ZÀ-ÿ0-9\s,.-]/g, '');
            $(this).val(value);
        });
    },

    setupTextFormatting: function() {
        // Nome do item
        $('input[name="nome_item"]').on('input', function() {
            let value = $(this).val();
            value = value.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
            $(this).val(value);
        });

        // Campo "a partir de quando"
        $('input[name="ate_partir_dia"]').on('input', function() {
            let value = $(this).val();
            value = value.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
            $(this).val(value);
        });
    }
};