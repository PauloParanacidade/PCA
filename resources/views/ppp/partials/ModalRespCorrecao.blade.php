{{-- Modal para Responder Correção --}}
<div class="modal fade" id="modalRespCorrecao" tabindex="-1" role="dialog" aria-labelledby="modalRespCorrecaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalRespCorrecaoLabel">
                    <i class="fas fa-edit mr-2"></i>
                    Enviar Correção/Justificativa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form action="{{ route('ppp.responderCorrecao', $ppp->id ?? 0) }}" method="POST" id="formRespCorrecao">
                @csrf
                @method('PUT')
                
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Atenção:</strong> Descreva as correções realizadas e/ou justificativas necessárias para o reenvio deste PPP.
                    </div>
                    
                    <div class="form-group">
                        <label for="justificativa">
                            <strong>Justificativa/Correções Realizadas <span class="text-danger">*</span></strong>
                        </label>
                        <textarea 
                            class="form-control @error('justificativa') is-invalid @enderror" 
                            id="justificativa" 
                            name="justificativa" 
                            rows="6" 
                            placeholder="Descreva as correções realizadas e/ou justificativas para o reenvio..."
                            maxlength="1000"
                            required>{{ old('justificativa') }}</textarea>
                        
                        <small class="form-text text-muted">
                            <span id="charCount">0</span>/1000 caracteres
                        </small>
                        
                        @error('justificativa')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar Correção
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Contador de caracteres
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('justificativa');
    const charCount = document.getElementById('charCount');
    
    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
        
        // Inicializar contador
        charCount.textContent = textarea.value.length;
    }
});


$(document).ready(function() {
    // Validação específica para o modal de correção
    $('#formRespCorrecao').on('submit', function(e) {
        const justificativa = $('#justificativa').val().trim();
        
        if (justificativa.length < 10) {
            e.preventDefault();
            alert('A justificativa deve ter pelo menos 10 caracteres.');
            return false;
        }
        
        if (justificativa.length > 1000) {
            e.preventDefault();
            alert('A justificativa não pode exceder 1000 caracteres.');
            return false;
        }
        
        // Desabilitar botão para evitar duplo envio
        $(this).find('button[type="submit"]').prop('disabled', true);
        
        return true;
    });
});

</script>