import $ from 'jquery';
import 'jquery-maskmoney/dist/jquery.maskMoney.min';

console.log("🚀 ppp-form.js carregado");
/**
 * PPP Form JavaScript
 * Funcionalidades específicas para formulário PPP
 */

const PPPForm = {
    // Configurações
    config: {
        autoSaveInterval: 30000, // 30 segundos
        formId: 'ppp-form-draft'
    },

    // Módulo de máscaras
    masks: {
        init: function() {
            // Máscaras de dinheiro
            $('.estimativa_valor, .valor_contrato_atualizado').maskMoney({
                prefix: 'R$ ',
                allowNegative: false,
                thousands: '.',
                decimal: ',',
                affixesStay: true,
                allowZero: true,
                precision: 2
            });

            // Aplicar máscaras de dinheiro
            $('.estimativa_valor, .valor_contrato_atualizado').maskMoney('mask');

            // Máscara para número de contrato
            $('#num_contrato').mask('0000/0000', {
                placeholder: '____/____',
                translation: {
                    '0': {pattern: /[0-9]/}
                }
            });

            // Formatação automática para valores monetários
            $('.estimativa_valor, .valor_contrato_atualizado').on('keyup', function() {
                $(this).maskMoney('mask');
            });
        },

        // Máscara para quantidade
        setupQuantityMask: function() {
            $('input[name="quantidade"]').on('input', function() {
                let value = $(this).val();
                value = value.replace(/[^a-zA-ZÀ-ÿ0-9\s,.-]/g, '');
                $(this).val(value);
            });
        },

        // Formatação de texto
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
    },

    // Módulo de validação
    // No módulo validation.init, adicionar especificamente os campos monetários
validation: {
    init: function() {
        // Validação em tempo real para todos os campos obrigatórios
        $('input[required], select[required], textarea[required]').on('blur', function() {
            PPPForm.validation.validateField($(this));
        });
        
        // Validação específica para campos monetários (mesmo não sendo required)
        $('.estimativa_valor, .valor_contrato_atualizado').on('blur', function() {
            PPPForm.validation.validateField($(this));
        });

        // Limpar validação ao focar
        $('input, select, textarea').on('focus', function() {
            $(this).removeClass('is-invalid is-valid').next('.invalid-feedback').remove();
        });
    },
    validateField: function(field) {
        const value = field.val().trim();
        
        if (value === '' || value === null) {
            field.addClass('is-invalid');
            if (!field.next('.invalid-feedback').length) {
                field.after('<div class="invalid-feedback">Este campo é obrigatório.</div>');
            }
        } else {
            field.removeClass('is-invalid').next('.invalid-feedback').remove();
            field.addClass('is-valid');
        }
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
},

    // Módulo de campos condicionais
    conditionalFields: {
        init: function() {
            // Vinculação
            $('#vinculacao_item').on('change', function() {
                PPPForm.conditionalFields.toggleVinculacao($(this).val() === 'Sim');
            });

            // Contrato vigente
            $('#tem_contrato_vigente').on('change', function() {
                PPPForm.conditionalFields.toggleContrato($(this).val() === 'Sim');
            });

            // Prorrogável
            $('#contrato_prorrogavel').on('change', function() {
                PPPForm.conditionalFields.toggleProrrogacao($(this).val() === 'Sim');
            });

            // Triggers iniciais
            $('#tem_contrato_vigente, #contrato_prorrogavel, #vinculacao_item').trigger('change');
        },

        toggleContrato: function(temContrato) {
            const camposContrato = $('#campos_contrato');
            const inputs = camposContrato.find('input, select');
            
            // CORREÇÃO: Quando TEM contrato (Sim), mostrar os campos
            if (temContrato) {
                camposContrato.slideDown(300);
                inputs.prop('disabled', false);
                $('#num_contrato, #contrato_prorrogavel').prop('required', true);
            } else {
                camposContrato.slideUp(300);
                inputs.prop('disabled', true).prop('required', false).val('')
                      .removeClass('is-invalid is-valid').next('.invalid-feedback').remove();
                $('#campo_pretensao_prorrogacao').slideUp(300);
                $('#renov_contrato').prop('required', false).val('');
            }
        },

        toggleVinculacao: function(isSim) {
            const campoJustificativa = $('#campo_justificativa_vinculacao');
            const textarea = $('#justificativa_vinculacao');
            
            // CORREÇÃO: Quando tem vinculação (Sim), mostrar o campo
            if (isSim) {
                campoJustificativa.slideDown(300);
                textarea.prop('required', true);
            } else {
                campoJustificativa.slideUp(300);
                textarea.prop('required', false).val('').removeClass('is-invalid is-valid').next('.invalid-feedback').remove();
            }
        },

        toggleProrrogacao: function(isProrrogavel) {
            const campoPretensao = $('#campo_pretensao_prorrogacao');
            const selectPretensao = $('#renov_contrato');
            
            if (isProrrogavel) {
                campoPretensao.slideDown(300);
                selectPretensao.prop('disabled', false).prop('required', true);
            } else {
                campoPretensao.slideUp(300);
                selectPretensao.prop('disabled', true).prop('required', false).val('')
                              .removeClass('is-invalid is-valid').next('.invalid-feedback').remove();
            }
        }
    },

    // Módulo de contadores de caracteres
    charCounters: {
        init: function() {
            $('textarea').each(function() {
                PPPForm.charCounters.setupCounter($(this));
            });
        },

        setupCounter: function(textarea) {
            const maxLength = textarea.attr('maxlength') || 1000;
            
            if (!textarea.next('.char-counter').length) {
                textarea.after(`<small class="char-counter text-muted float-end mt-1">0/${maxLength} caracteres</small>`);
            }
            
            textarea.on('input', function() {
                PPPForm.charCounters.updateCounter($(this), maxLength);
            });
            
            textarea.trigger('input');
        },

        updateCounter: function(textarea, maxLength) {
            const currentLength = textarea.val().length;
            const counter = textarea.next('.char-counter');
            counter.text(`${currentLength}/${maxLength} caracteres`);
            
            // Mudar cor baseado no uso
            if (currentLength > maxLength * 0.9) {
                counter.removeClass('text-muted').addClass('text-warning');
            } else if (currentLength === maxLength) {
                counter.removeClass('text-warning').addClass('text-danger');
            } else {
                counter.removeClass('text-warning text-danger').addClass('text-muted');
            }
        }
    },

    // Módulo de auto-save
    autoSave: {
        init: function() {
            // Auto-save a cada 30 segundos
            setInterval(function() {
                PPPForm.autoSave.saveToLocalStorage();
            }, PPPForm.config.autoSaveInterval);

            // Limpar rascunho após envio
            $('form').on('submit', function() {
                PPPForm.autoSave.clearDraft();
            });
        },

        saveToLocalStorage: function() {
            const formData = {};
            $('input, select, textarea').each(function() {
                if ($(this).attr('name') && $(this).val()) {
                    formData[$(this).attr('name')] = $(this).val();
                }
            });
            localStorage.setItem(PPPForm.config.formId, JSON.stringify(formData));
        },

        clearDraft: function() {
            localStorage.removeItem(PPPForm.config.formId);
        }
    },

    // Módulo de botões e submissão
    buttons: {
        init: function() {
            // Botão salvar rascunho
            $('#btn-salvar-rascunho').on('click', function() {
                PPPForm.buttons.handleSaveDraft($(this));
            });

            // Submissão do formulário
            $('form').on('submit', function(e) {
                return PPPForm.buttons.handleSubmit(e, $(this));
            });
        },

        handleSaveDraft: function(btn) {
            const originalText = btn.html();
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Salvando...');
            
            // Simular salvamento (implementar lógica AJAX aqui)
            setTimeout(function() {
                btn.prop('disabled', false).html('<i class="fas fa-check me-2"></i>Salvo!');
                
                setTimeout(function() {
                    btn.html(originalText);
                }, 2000);
            }, 1000);
        },

        handleSubmit: function(e, form) {
            const submitBtn = form.find('button[type="submit"]');
            
            console.log('Dados do formulário:', form.serialize());
            
            // Validar campos obrigatórios
            const camposVazios = PPPForm.validation.validateForm();
            
            if (camposVazios.length > 0) {
                e.preventDefault();
                console.log('Campos obrigatórios vazios:', camposVazios);
                alert('Campos obrigatórios não preenchidos: ' + camposVazios.join(', '));
                return false;
            }
            
            submitBtn.prop('disabled', true)
                   .html('<i class="fas fa-spinner fa-spin me-2"></i>Salvando...');
            
            return true;
        }
    },

    // Inicialização principal
    init: function() {
        $(function() {
            // PPPForm.masks.init();
            // PPPForm.masks.setupQuantityMask();
            // PPPForm.masks.setupTextFormatting();
            PPPForm.validation.init();
            PPPForm.conditionalFields.init();
            PPPForm.charCounters.init();
            PPPForm.autoSave.init();
            PPPForm.buttons.init();
        });
    }
};

// Inicializar quando o documento estiver pronto
PPPForm.init();