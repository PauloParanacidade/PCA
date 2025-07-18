/**
 * Templates HTML
 * Centraliza todos os templates HTML usados no formulário PPP
 */
export const HtmlTemplates = {
    getProgressIndicator: function(currentStep, totalSteps, cards) {
        return `
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-primary">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-tasks me-2"></i>
                                    Progresso do Formulário
                                </h6>
                                <span class="badge bg-primary" id="progress-badge">
                                    Etapa ${currentStep} de ${totalSteps}
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" id="progress-bar" 
                                     role="progressbar" style="width: ${(currentStep / totalSteps) * 100}%"
                                     aria-valuenow="${currentStep}" aria-valuemin="0" aria-valuemax="${totalSteps}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                ${cards.map(card => `
                                    <small class="text-muted step-indicator" data-step="${card.step}">
                                        <i class="fas fa-circle me-1 step-icon" data-step="${card.step}"></i>
                                        ${card.title}
                                    </small>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    getNavigationButtons: function(step, totalSteps) {
        if (step === 1) {
            return `
                <div class="card-navigation mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between">
                        <a href="${window.location.origin}/dashboard" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="button" class="btn btn-primary btn-next" data-step="1">
                            Avançar<i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            `;
        } else if (step === 2) {
            return `
                <div class="card-navigation mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="button" class="btn btn-outline-secondary btn-prev me-2" data-step="2">
                                <i class="fas fa-arrow-left me-2"></i>Anterior
                            </button>
                            <a href="${window.location.origin}/dashboard" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                        <button type="button" class="btn btn-primary btn-next" data-step="2">
                            Avançar<i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            `;
        }
        return '';
    },

    getFinalButtons: function() {
        return `
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-4">
                            <button type="button" class="btn btn-outline-secondary btn-prev me-3" data-step="3">
                                <i class="fas fa-arrow-left me-2"></i>Anterior
                            </button>
                            <a href="${window.location.origin}/dashboard" class="btn btn-secondary me-3">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-paper-plane me-2"></i>Salvar e Enviar para Aprovação
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    getSuccessNotification: function(message) {
        return `
            <div class="alert alert-success alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" 
                 id="success-notification">
                <i class="fas fa-check-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }
};