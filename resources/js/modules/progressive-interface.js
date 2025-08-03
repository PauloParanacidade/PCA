/**
 * Progressive Interface Module
 * Gerencia a exibição progressiva dos cards do formulário PPP
 * 
 * Funcionalidade:
 * - Inicialmente mostra apenas o card azul
 * - Após salvar, exibe os demais cards com animação
 * - Controla a visibilidade e estado dos cards
 */

export const ProgressiveInterface = {
    config: {
        cards: {
            blue: '#card-identificacao',
            yellow: '#card-detalhamento', 
            cyan: '#card-justificativa',
            green: '#card-orcamento'
        },
        animationDuration: 500
    },

    /**
     * Inicializa a interface progressiva
     */
    init: function() {
        console.log('🎨 ProgressiveInterface: Inicializando...');
        
        // Verifica se estamos em modo de criação (novo PPP)
        if (this.isCreationMode()) {
            this.setupInitialState();
            this.bindEvents();
        } else {
            // Modo de edição - mostra todos os cards
            this.showAllCards();
        }
    },

    /**
     * Verifica se estamos em modo de criação
     */
    isCreationMode: function() {
        // Verifica se a URL contém 'create' ou se não há ID no formulário
        const url = window.location.href;
        const hasCreateInUrl = url.includes('/create') || url.includes('/novo');
        const hasId = $('input[name="id"]').val();
        
        return hasCreateInUrl || !hasId;
    },

    /**
     * Configura o estado inicial (apenas card azul visível)
     */
    setupInitialState: function() {
        console.log('🎨 ProgressiveInterface: Configurando estado inicial...');
        
        // Mostra apenas o card azul
        $(this.config.cards.blue).show();
        
        // Oculta os demais cards
        $(this.config.cards.yellow).hide();
        $(this.config.cards.cyan).hide();
        $(this.config.cards.green).hide();
        
        // Adiciona classe para identificar estado inicial
        $('body').addClass('ppp-initial-state');
    },

    /**
     * Mostra todos os cards (modo edição)
     */
    showAllCards: function() {
        console.log('🎨 ProgressiveInterface: Mostrando todos os cards...');
        
        Object.values(this.config.cards).forEach(cardSelector => {
            $(cardSelector).show();
        });
        
        $('body').removeClass('ppp-initial-state').addClass('ppp-edit-state');
    },

    /**
     * Vincula eventos necessários
     */
    bindEvents: function() {
        const self = this;
        
        // Escuta o evento de salvamento bem-sucedido do card azul
        $(document).on('ppp:card-blue-saved', function(event, data) {
            console.log('🎨 ProgressiveInterface: Card azul salvo, revelando demais cards...');
            self.revealRemainingCards();
        });
        
        // Escuta cliques no botão salvar do card azul
        $(document).on('click', '#btn-save-blue-card', function(e) {
            // Este evento será disparado pelos outros módulos após salvamento
        });
    },

    /**
     * Revela os cards restantes com animação
     */
    revealRemainingCards: function() {
        console.log('🎨 ProgressiveInterface: Revelando cards restantes...');
        
        const cardsToReveal = [
            this.config.cards.yellow,
            this.config.cards.cyan,
            this.config.cards.green
        ];
        
        // Remove estado inicial
        $('body').removeClass('ppp-initial-state').addClass('ppp-progressive-state');
        
        // Revela cards com animação sequencial
        cardsToReveal.forEach((cardSelector, index) => {
            setTimeout(() => {
                $(cardSelector)
                    .hide()
                    .slideDown(this.config.animationDuration, 'easeInOut')
                    .addClass('card-revealed');
                    
                // Adiciona efeito de destaque
                setTimeout(() => {
                    $(cardSelector).addClass('card-highlight');
                    setTimeout(() => {
                        $(cardSelector).removeClass('card-highlight');
                    }, 1000);
                }, 200);
                
            }, index * 200); // Delay progressivo entre cards
        });
        
        // Scroll suave para o próximo card
        setTimeout(() => {
            this.scrollToCard(this.config.cards.yellow);
        }, 300);
    },

    /**
     * Faz scroll suave para um card específico
     */
    scrollToCard: function(cardSelector) {
        const $card = $(cardSelector);
        if ($card.length) {
            $('html, body').animate({
                scrollTop: $card.offset().top - 100
            }, 800, 'easeInOut');
        }
    },

    /**
     * Força a exibição de todos os cards (usado em casos especiais)
     */
    forceShowAllCards: function() {
        console.log('🎨 ProgressiveInterface: Forçando exibição de todos os cards...');
        this.showAllCards();
    },

    /**
     * Reseta para o estado inicial
     */
    resetToInitialState: function() {
        console.log('🎨 ProgressiveInterface: Resetando para estado inicial...');
        this.setupInitialState();
    }
};

// Adiciona estilos CSS necessários dinamicamente
const addProgressiveStyles = () => {
    const styles = `
        <style id="progressive-interface-styles">
            .card-revealed {
                opacity: 1 !important;
                transform: translateY(0) !important;
            }
            
            .card-highlight {
                box-shadow: 0 0 20px rgba(0, 123, 255, 0.3) !important;
                border: 2px solid #007bff !important;
                transition: all 0.3s ease !important;
            }
            
            .ppp-initial-state .card:not(#card-identificacao) {
                display: none !important;
            }
            
            .ppp-progressive-state .card {
                transition: all 0.5s ease;
            }
            
            .ppp-edit-state .card {
                display: block !important;
            }
        </style>
    `;
    
    if (!$('#progressive-interface-styles').length) {
        $('head').append(styles);
    }
};

// Adiciona estilos quando o módulo é carregado
$(document).ready(() => {
    addProgressiveStyles();
});