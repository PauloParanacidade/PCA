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

<!-- Esse campo envia o valor -->
<input type="hidden" name="acao" id="inputAcao" value="rascunho">

<!-- Este botão apenas dispara o submit -->
<button type="submit" class="btn btn-primary btn-lg px-4" data-acao="enviar" id="btnUpdate">
    <i class="fas fa-paper-plane me-2"></i>
    {{ $edicao ? 'Salvar e Enviar para Aprovação' : 'Salvar e Enviar para Aprovação' }}
</button>


            </div>
        </div>
    </div>
</div>
