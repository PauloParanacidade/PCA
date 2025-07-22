<div class="row mt-4" id="botoes-finais">
    <div class="col-12">
        <div class="d-flex justify-content-center flex-wrap gap-3">
            
            {{-- Botão Cancelar --}}
            @if(!isset($ppp) || !$ppp->id)
            {{-- Modo criação: retorna para dashboard --}}
            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg mx-2">
                <i class="fas fa-times me-2"></i>
                Cancelar
            </a>
            @else
            {{-- Modo edição: retorna para tela anterior --}}
            <button type="button" onclick="history.back()" class="btn btn-secondary btn-lg mx-2">
                <i class="fas fa-arrow-left me-2"></i>
                Voltar
            </button>
            @endif
            
            {{-- Botões de ação --}}
            {{-- Sempre exibe o botão "Salvar e Enviar para Avaliação" no modo criação. Ele só será ocultado via JS --}}
            <button type="submit" id="btn-salvar-enviar" name="acao" value="enviar_aprovacao"
                class="btn btn-primary btn-lg mx-2" 
                style="{{ isset($ppp) && $ppp->id ? '' : 'display: none;' }}">
                <i class="fas fa-paper-plane me-2"></i>
                Salvar e Enviar para Avaliação
            </button>
        
            {{-- Só exibe o botão Salvar no modo edição --}}
            @if (isset($ppp) && $ppp->id && !isset($isCreating))
                <button type="submit" name="acao" value="salvar" class="btn btn-success btn-lg mx-2">
                    <i class="fas fa-save me-2"></i>
                    Salvar
                </button>
            @endif
        </div>
    </div>
</div>






