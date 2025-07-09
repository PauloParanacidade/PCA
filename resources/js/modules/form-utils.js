import $ from 'jquery';

/**
 * Módulo de Utilitários do Formulário
 * Contém funcionalidades auxiliares como contadores, auto-save, etc.
 */
export const FormUtils = {
    init: function() {
        this.initCharCounters();
        this.initAutoSave();
    },

    initCharCounters: function() {
        $('textarea').each((index, element) => {
            this.setupCounter($(element));
        });
    },

    setupCounter: function(textarea) {
        const maxLength = textarea.attr('maxlength') || 1000;
        
        if (!textarea.next('.char-counter').length) {
            textarea.after(`<small class="char-counter text-muted float-end mt-1">0/${maxLength} caracteres</small>`);
        }
        
        textarea.on('input', () => {
            this.updateCounter(textarea, maxLength);
        });
        
        textarea.trigger('input');
    },

    updateCounter: function(textarea, maxLength) {
        const currentLength = textarea.val().length;
        const counter = textarea.next('.char-counter');
        counter.text(`${currentLength}/${maxLength} caracteres`);
        
        if (currentLength > maxLength * 0.9) {
            counter.removeClass('text-muted').addClass('text-warning');
        } else if (currentLength === maxLength) {
            counter.removeClass('text-warning').addClass('text-danger');
        } else {
            counter.removeClass('text-warning text-danger').addClass('text-muted');
        }
    },

    initAutoSave: function() {
        setInterval(() => {
            this.saveToLocalStorage();
        }, 30000);

        $('form').on('submit', () => {
            this.clearDraft();
        });
    },

    saveToLocalStorage: function() {
        const formData = {};
        $('input, select, textarea').each(function() {
            if ($(this).attr('name') && $(this).val()) {
                formData[$(this).attr('name')] = $(this).val();
            }
        });
        localStorage.setItem('ppp-form-draft', JSON.stringify(formData));
    },

    clearDraft: function() {
        localStorage.removeItem('ppp-form-draft');
    }
};