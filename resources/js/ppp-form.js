import $ from 'jquery';
import './money-mask.js';

// Importar módulos
import { ProgressiveInterface } from './modules/progressive-interface.js';
import { FormValidation } from './modules/form-validation.js';
import { ConditionalFields } from './modules/conditional-fields.js';
import { FormUtils } from './modules/form-utils.js';
import { FormButtons } from './modules/form-buttons.js';
// Importa o money-mask.js
import { MoneyMask } from './money-mask.js';

console.log("🚀 ppp-form.js carregado");

/**
 * PPP Form JavaScript - Arquivo Principal
 * Orquestra todos os módulos do formulário PPP
 */
const PPPForm = {
    config: {
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
            MoneyMask.init();
        });
    }
};

// $(function() {
//     PPPForm.init();
// });

export default PPPForm;