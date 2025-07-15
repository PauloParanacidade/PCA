<div class="row mt-4" id="botoes-finais">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            {{-- Botão Cancelar --}}
            <div>
                @if(!isset($ppp) || !$ppp->id)
                    {{-- Modo criação: retorna para dashboard --}}
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times me-2"></i>
                        Cancelar
                    </a>
                @else
                    {{-- Modo edição: retorna para tela anterior --}}
                    <button type="button" onclick="history.back()" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>
                        Cancelar
                    </button>
                @endif
            </div>
            
            {{-- Botões de ação --}}
            <div>
                @if(!isset($ppp) || !$ppp->id)
                    {{-- Modo criação: botão aparece após clicar em Próximo --}}
                    <button type="submit" id="btn-salvar-enviar" class="btn btn-success btn-lg" style="display: none;">
                        <i class="fas fa-paper-plane me-2"></i>
                        Salvar e Enviar para Avaliação
                    </button>
                @else
                    {{-- Modo edição: botões normais --}}
                    <div class="btn-group">
                        <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#historicoModal">
                            <i class="fas fa-history me-2"></i>
                            Histórico
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>
                            Salvar
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnProximo = document.getElementById('btn-proximo-card-azul');
    const btnSalvarEnviar = document.getElementById('btn-salvar-enviar');
    const cardsAdicionais = document.getElementById('cards-adicionais');
    
    if (btnProximo) {
        btnProximo.addEventListener('click', function() {
            // Validar campos obrigatórios do card azul
            const camposObrigatorios = [
                'nome_item',
                'quantidade', 
                'categoria',
                'grau_prioridade',
                'previsao_contratacao',
                'descricao_especificacao'
            ];
            
            let todosPreenchidos = true;
            
            camposObrigatorios.forEach(function(campo) {
                const elemento = document.querySelector(`[name="${campo}"]`);
                if (elemento && !elemento.value.trim()) {
                    elemento.classList.add('is-invalid');
                    todosPreenchidos = false;
                } else if (elemento) {
                    elemento.classList.remove('is-invalid');
                }
            });
            
            if (todosPreenchidos) {
                // Mostrar cards adicionais com animação
                if (cardsAdicionais) {
                    cardsAdicionais.style.display = 'block';
                    cardsAdicionais.classList.add('fade-in-cards');
                }
                
                // Esconder botão próximo
                btnProximo.style.display = 'none';
                
                // Mostrar botão salvar e enviar
                if (btnSalvarEnviar) {
                    btnSalvarEnviar.style.display = 'inline-block';
                }
                
                // // Scroll suave para os novos cards
                // setTimeout(() => {
                //     cardsAdicionais.scrollIntoView({ 
                //         behavior: 'smooth',
                //         block: 'start'
                //     });
                // }, 300);
            } else {
                // Mostrar alerta de campos obrigatórios
                alert('Por favor, preencha todos os campos obrigatórios antes de continuar.');
            }
        });
    }
});
</script>