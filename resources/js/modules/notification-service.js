import $ from 'jquery';
import { HtmlTemplates } from './html-templates.js';

/**
 * Serviço de Notificações
 * Gerencia exibição de notificações de sucesso, erro, etc.
 */
export const NotificationService = {
    showSuccess: function(message) {
        const notificationHtml = HtmlTemplates.getSuccessNotification(message);
        $('body').append(notificationHtml);
        
        setTimeout(() => {
            $('#success-notification').fadeOut(500, function() {
                $(this).remove();
            });
        }, 4000);
    },

    showError: function(message) {
        const notificationHtml = `
            <div class="alert alert-danger alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" 
                 id="error-notification">
                <i class="fas fa-exclamation-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('body').append(notificationHtml);
        
        setTimeout(() => {
            $('#error-notification').fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    },

    showWarning: function(message) {
        const notificationHtml = `
            <div class="alert alert-warning alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" 
                 id="warning-notification">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('body').append(notificationHtml);
        
        setTimeout(() => {
            $('#warning-notification').fadeOut(500, function() {
                $(this).remove();
            });
        }, 4500);
    }
};