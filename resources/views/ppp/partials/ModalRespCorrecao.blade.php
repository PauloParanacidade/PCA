{{-- DEBUG: Verificar se $ppp está definida --}}
@if(isset($ppp))
    <!-- PPP ID: {{ $ppp->id }} - Status: {{ $ppp->status_id }} -->
@else
    <!-- ERRO: Variável $ppp não está definida -->
@endif

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
    console.log('🔍 MODAL DEBUG - Script carregado');
    
    const textarea = document.getElementById('justificativa');
    const charCount = document.getElementById('charCount');
    
    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
        
        // Inicializar contador
        charCount.textContent = textarea.value.length;
        console.log('✅ MODAL DEBUG - Contador de caracteres inicializado');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 MODAL DEBUG - Event listeners sendo configurados');
    
    // Verificar se o formulário existe
    const form = document.getElementById('formRespCorrecao');
    if (!form) {
        console.error('❌ MODAL DEBUG - Formulário #formRespCorrecao não encontrado!');
        return;
    }
    console.log('✅ MODAL DEBUG - Formulário encontrado:', form);
    
    // Event listener para submit do formulário
    form.addEventListener('submit', function(e) {
        console.log('🚀 MODAL DEBUG - Submit do formulário detectado!');
        console.log('🔍 MODAL DEBUG - Event object:', e);
        console.log('🔍 MODAL DEBUG - Form action:', this.action);
        console.log('🔍 MODAL DEBUG - Form method:', this.method);
        
        const justificativa = document.getElementById('justificativa').value.trim();
        console.log('🔍 MODAL DEBUG - Justificativa length:', justificativa.length);
        console.log('🔍 MODAL DEBUG - Justificativa content:', justificativa);
        
        if (justificativa.length < 10) {
            console.log('❌ MODAL DEBUG - Validação falhou: menos de 10 caracteres');
            e.preventDefault();
            alert('A justificativa deve ter pelo menos 10 caracteres.');
            return false;
        }
        
        if (justificativa.length > 1000) {
            console.log('❌ MODAL DEBUG - Validação falhou: mais de 1000 caracteres');
            e.preventDefault();
            alert('A justificativa não pode exceder 1000 caracteres.');
            return false;
        }
        
        console.log('✅ MODAL DEBUG - Validação passou, enviando formulário');
        console.log('🔍 MODAL DEBUG - Dados do FormData:');
        
        const formData = new FormData(this);
        for (let [key, value] of formData.entries()) {
            console.log(`🔍 MODAL DEBUG - ${key}: ${value}`);
        }
        
        return true;
    });
    
    // Event listener para o botão submit específico
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        console.log('✅ MODAL DEBUG - Botão submit encontrado:', submitButton);
        submitButton.addEventListener('click', function(e) {
            console.log('🔍 MODAL DEBUG - Botão submit clicado!');
            console.log('🔍 MODAL DEBUG - Button element:', this);
        });
    } else {
        console.error('❌ MODAL DEBUG - Botão submit não encontrado!');
    }
    
    // Event listener para abertura do modal
    $('#modalRespCorrecao').on('show.bs.modal', function (e) {
        console.log('🔍 MODAL DEBUG - Modal sendo aberto');
        console.log('🔍 MODAL DEBUG - Modal element:', this);
    });
    
    $('#modalRespCorrecao').on('shown.bs.modal', function (e) {
        console.log('✅ MODAL DEBUG - Modal totalmente aberto');
    });
});
</script>