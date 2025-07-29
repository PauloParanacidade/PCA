import $ from 'jquery';

/**
 * Formata campo monetário - Versão Simplificada
 */
function formataMoeda(campo) {
    let valor = campo.value;
    valor = valor.replace(/\D/g, ''); // Remove caracteres não numéricos
    
    if (!valor) {
        campo.value = '';
        return;
    }
    
    valor = (valor / 100).toFixed(2) + ''; // Converte para float e formata
    valor = valor.replace(".", ",");
    
    let parteInteira = valor.split(",")[0];
    let parteDecimal = valor.split(",")[1];
    
    // Adiciona pontos como separadores de milhares
    parteInteira = parteInteira.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    
    campo.value = "R$ " + parteInteira + "," + parteDecimal;
}

/**
 * Módulo MoneyMask simplificado
 */
export const MoneyMask = {
    init: function() {
        // Aplica a máscara em campos existentes
        $('.money-field').each(function() {
            $(this).on('keyup input', function() {
                formataMoeda(this);
            });
            
            // Formata valor inicial se existir
            if ($(this).val()) {
                formataMoeda(this);
            }
        });
        
        // Observer para campos adicionados dinamicamente
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        const $node = $(node);
                        
                        // Verifica se o próprio nó é um campo money
                        if ($node.hasClass('money-field')) {
                            $node.on('keyup input', function() {
                                formataMoeda(this);
                            });
                        }
                        
                        // Verifica campos filhos
                        $node.find('.money-field').each(function() {
                            $(this).on('keyup input', function() {
                                formataMoeda(this);
                            });
                        });
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    },
    
    /**
     * Obtém valor numérico de um campo formatado
     */
    getNumericValue: function($field) {
        let value = $field.val();
        if (!value) return 0;
        
        // Remove R$, pontos e substitui vírgula por ponto
        let numericValue = value
            .replace(/R\$\s?/, '')
            .replace(/\./g, '')
            .replace(',', '.');
            
        return parseFloat(numericValue) || 0;
    }
};

// Inicialização automática
$(function() {
    MoneyMask.init();
});
