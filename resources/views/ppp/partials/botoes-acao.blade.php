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
            @if(!isset($ppp) || !$ppp->id)
                {{-- Botão Salvar e Enviar para Avaliação (só aparece após clicar em Avançar) --}}
                <button type="submit" id="btn-salvar-enviar" name="acao" value="enviar_aprovacao" class="btn btn-success btn-lg mx-2" style="display: none;">
                    <i class="fas fa-paper-plane me-2"></i>
                    Salvar e Enviar para Avaliação
                </button>
            @endif

        </div>
    </div>
</div>

@push('js')
<script>

</script>
@endpush


<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnAvancar = document.getElementById('btn-avancar-card-azul');
    const btnSalvarEnviar = document.getElementById('btn-salvar-enviar');

    if (btnAvancar) {
        btnAvancar.addEventListener('click', function() {
            const camposObrigatorios = [
                'nome_item',
                'quantidade', 
                'grau_prioridade',
                'descricao',
                'natureza_objeto',
                'justificativa_pedido',
                'categoria'
            ];

            let todosPreenchidos = true;
            camposObrigatorios.forEach(function(campo) {
                const elemento = document.querySelector(`[name="${campo}"]`);
                if (!elemento || !elemento.value.trim()) {
                    if (elemento) elemento.classList.add('is-invalid');
                    todosPreenchidos = false;
                } else if (elemento) {
                    elemento.classList.remove('is-invalid');
                }
            });
            
            if (todosPreenchidos) {
                // Desbloquear cards com animação
                const cardsParaDesbloquear = document.querySelectorAll('.card-bloqueado.bloqueado');

                cardsParaDesbloquear.forEach((card, index) => {
                    setTimeout(() => {
                        card.classList.add('desbloqueando');

                        setTimeout(() => {
                            card.classList.remove('bloqueado', 'desbloqueando');
                            card.classList.add('card-desbloqueado');
                        }, 300);
                    }, index * 200);
                });

                // Esconder botão avançar
                btnAvancar.style.transition = 'all 0.3s ease';
                btnAvancar.style.opacity = '0';
                btnAvancar.style.transform = 'translateY(-10px)';

                setTimeout(() => {
                    btnAvancar.style.display = 'none';
                }, 300);

                // Mostrar botão salvar e enviar
                if (btnSalvarEnviar) {
                    setTimeout(() => {
                        btnSalvarEnviar.style.display = 'inline-block';
                        btnSalvarEnviar.style.opacity = '0';
                        btnSalvarEnviar.style.transform = 'translateY(10px)';

                        setTimeout(() => {
                            btnSalvarEnviar.style.transition = 'all 0.3s ease';
                            btnSalvarEnviar.style.opacity = '1';
                            btnSalvarEnviar.style.transform = 'translateY(0)';
                        }, 50);
                    }, 800);
                }

            } else {
                alert('Por favor, preencha todos os campos obrigatórios antes de continuar.');
            }
        });
    }
});
</script>
