<div class="row mt-4" id="botoes-finais">
    <div class="col-12">
        <div class="d-flex justify-content-center flex-wrap gap-3">

            {{-- Botão Cancelar ou Voltar --}}
            @if($isCreating)
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

            {{-- Modo CRIAÇÃO: botão "Salvar e Enviar para Avaliação" --}}
            @if($isCreating)
                <button type="submit" id="btn-salvar-enviar" name="acao" value="enviar_aprovacao"
                    class="btn btn-primary btn-lg mx-2"
                    {{-- Mostrar botão se PPP existe e está em rascunho --}}
                    style="{{ (isset($ppp) && $ppp->id && $ppp->status_id == 1) ? 'display: inline-block;' : 'display: none;' }}">
                    <i class="fas fa-paper-plane me-2"></i>
                    Salvar e Enviar para Avaliação
                </button>
            @endif

            {{-- Modo EDIÇÃO: botão condicional baseado no status --}}
            @if(!$isCreating && isset($ppp) && $ppp->id)
                {{-- Se status for "aguardando correção" (4) ou "em correção" (5) --}}
                @if(in_array($ppp->status_id, [4, 5]))
                    <button type="button" class="btn btn-primary btn-lg mx-2" data-toggle="modal" data-target="#modalRespCorrecao">
                        <i class="fas fa-edit me-2"></i>
                        Enviar Correção/Justificativa
                    </button>
                @elseif($ppp->status_id == 5) {{-- Status "em correção" (mantido para compatibilidade) --}}
                    <button type="submit" name="acao" value="enviar_aprovacao" class="btn btn-primary btn-lg mx-2">
                        <i class="fas fa-paper-plane me-2"></i>
                        Salvar e Enviar para Avaliação
                    </button>
                @else
                    <button type="submit" name="acao" value="salvar" class="btn btn-success btn-lg mx-2">
                        <i class="fas fa-save me-2"></i>
                        Salvar
                    </button>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Incluir a modal de correção se estiver no modo de edição e status adequado --}}
@if(!$isCreating && isset($ppp) && $ppp->id && in_array($ppp->status_id, [4, 5]))
    @include('ppp.partials.ModalRespCorrecao')
@endif
