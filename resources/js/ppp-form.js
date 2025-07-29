import $ from 'jquery';


// Importar módulos
import { ProgressiveInterface } from './modules/progressive-interface.js';
import { FormValidation } from './modules/form-validation.js';
import { ConditionalFields } from './modules/conditional-fields.js';
import { FormUtils } from './modules/form-utils.js';
import { FormButtons } from './modules/form-buttons.js';

console.log("🚀 ppp-form.js carregado");

/**
 * PPP Form JavaScript - Arquivo Principal
 * Orquestra todos os módulos do formulário PPP
 */
const PPPForm = {
    config: {
        autoSaveInterval: 30000,
        formId: 'ppp-form-draft'
    },

    init: function() {
        $(function() {
            // Inicializar interface progressiva PRIMEIRO
            ProgressiveInterface.init();
            
            // Depois inicializar outros módulos
            FormValidation.init();
            ConditionalFields.init();
            FormUtils.init();
            FormButtons.init();
        });
    }
};

// Inicializar quando o documento estiver pronto
PPPForm.init();

export default PPPForm;