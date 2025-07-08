{{-- Botões de Ação --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-4">
                @if($edicao)
                    <a href="{{ route('ppp.index') }}" class="btn btn-secondary btn-lg me-3">
                        <i class="fas fa-arrow-left me-2"></i>
                        Cancelar
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg me-3">
                        <i class="fas fa-arrow-left me-2"></i>
                        Cancelar
                    </a>
                @endif
                
                <!-- Botão Salvar (apenas salvar como rascunho) -->
                <button type="button" class="btn btn-warning btn-lg me-3 px-4" id="btn-salvar-rascunho">
                    <i class="fas fa-save me-2"></i>
                    Salvar
                </button>
                
                <!-- Botão Salvar e Enviar para Aprovação -->
                <button type="submit" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-paper-plane me-2"></i>
                    {{ $edicao ? 'Salvar e Enviar para Aprovação' : 'Salvar e Enviar para Aprovação' }}
                </button>
            </div>
        </div>
    </div>
</div>