/**
 * Serviço de API
 * Centraliza todas as chamadas AJAX do formulário
 */
export const ApiService = {
    async savePartialPpp(formData) {
        try {
            const response = await fetch('/ppp', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                return await response.json();
            } else {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Erro no servidor');
            }
        } catch (error) {
            console.error('Erro ao salvar PPP:', error);
            throw error;
        }
    },

    async updatePpp(pppId, formData) {
        try {
            const response = await fetch(`/ppp/${pppId}`, {
                method: 'PUT',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                return await response.json();
            } else {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Erro no servidor');
            }
        } catch (error) {
            console.error('Erro ao atualizar PPP:', error);
            throw error;
        }
    }
};