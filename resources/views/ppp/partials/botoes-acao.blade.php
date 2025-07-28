<div class="row mt-4" id="botoes-finais">
    <div class="col-12">
        <div class="d-flex justify-content-center flex-wrap gap-3">

            {{-- Botão Cancelar ou Voltar --}}
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

            {{-- Modo CRIAÇÃO: botão "Salvar e Enviar para Avaliação" (inicialmente escondido, mostra via JS no passo final) --}}
            @if(isset($isCreating) && $isCreating)
                <button type="submit" id="btn-salvar-enviar" name="acao" value="enviar_aprovacao"
                    class="btn btn-primary btn-lg mx-2"
                    style="display: none;">
                    <i class="fas fa-paper-plane me-2"></i>
                    Salvar e Enviar para Avaliação
                </button>
            @endif

            {{-- Modo EDIÇÃO: botão "Salvar" (NÃO envia para aprovação, apenas atualiza) --}}
            @if(isset($ppp) && $ppp->id && isset($isCreating) && !$isCreating)
                <button type="submit" name="acao" value="salvar" class="btn btn-success btn-lg mx-2">
                    <i class="fas fa-save me-2"></i>
                    Salvar
                </button>
            @endif

        </div>
    </div>
</div>
