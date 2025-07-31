{{-- Seção 3: Informações Financeiras (Card Verde) --}}
<div class="col-12 mb-4">
    <div class="card card-outline card-success h-100">
        <div class="card-header bg-success">
            <h3 class="card-title text-white">
                <i class="fas fa-dollar-sign me-2"></i>
                Informações Financeiras
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-money-bill-wave text-success me-1"></i>
                        Valor total estimado (exercício) <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="estimativa_valor" class="form-control form-control-lg money-field" required
                    value="{{ old('estimativa_valor', isset($ppp->estimativa_valor) ? 'R$ ' . number_format($ppp->estimativa_valor, 2, ',', '.') : '') }}" 
                    placeholder="R$ 0,00" autocomplete="off">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-hand-holding-usd text-success me-1"></i>
                        Origem do recurso <span class="text-danger">*</span>
                    </label>
                    <select name="origem_recurso" class="form-control form-control-lg" required>
                        <option value="" disabled {{ old('origem_recurso', $ppp->origem_recurso ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                        @foreach (['PRC', 'FDU', 'BID/FDU'] as $origem)
                        <option value="{{ $origem }}" {{ old('origem_recurso', $ppp->origem_recurso ?? '') == $origem ? 'selected' : '' }}>{{ $origem }}</option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Campo condicional "Valor Total até o final do contrato" --}}
                <div id="campo-valor-mais-um-exercicio" class="col-12 mb-3" style="display: none; opacity: 0; transform: translateY(-20px); transition: all 0.5s ease-in-out;">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-plus text-success me-1"></i>
                        Valor Total até o final do contrato <span class="text-danger">*</span>
                        <i class="fas fa-question-circle text-muted ms-1"
                        tabindex="0"
                        role="button"
                        data-bs-toggle="popover"
                        data-bs-trigger="focus"
                        data-bs-placement="top"
                        data-bs-html="true"
                        title="Informe o valor total estimado
para toda a vigência do contrato, somando 
o exercício atual e os anos seguintes.
Este campo se aplica apenas a contratos 
com duração superior a um exercício."
                        data-bs-content="">
                    </i>
                    
                </label>
                
                <input type="text" name="valor_contrato_atualizado" id="valor_contrato_atualizado" class="form-control form-control-lg money-field"
                value="{{ old('valor_contrato_atualizado', isset($ppp->valor_contrato_atualizado) ? 'R$ ' . number_format($ppp->valor_contrato_atualizado, 2, ',', '.') : '') }}" 
                placeholder="R$ 0,00" autocomplete="off">
            </div>
            
            <div class="col-12 mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-clipboard-list text-success me-1"></i>
                    Justificativa do valor estimado <span class="text-danger">*</span>
                </label>
                <textarea name="justificativa_valor" class="form-control" rows="4" required
                placeholder="Justifique como chegou ao valor estimado...">{{ old('justificativa_valor', $ppp->justificativa_valor ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>
</div>

<style>
    /* Transições suaves para campos que se movem */
    .card, .form-group, .mb-3 {
        transition: all 0.3s ease-in-out;
    }
    
    .valor-mais-um-exercicio {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        transform-origin: top;
    }
    
    .valor-mais-um-exercicio.show {
        animation: slideDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .valor-mais-um-exercicio.hide {
        animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            max-height: 200px;
            transform: translateY(0);
        }
    }
    
    @keyframes slideUp {
        from {
            opacity: 1;
            max-height: 200px;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            max-height: 0;
            transform: translateY(-10px);
        }
    }
    
    /* Efeito suave para campos que se deslocam */
    .justificativa-valor, .card.bg-info {
        transition: transform 0.3s ease-in-out, margin-top 0.3s ease-in-out;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const campoValorMaisUm = document.getElementById('campo-valor-mais-um-exercicio');
        const inputValorMaisUm = document.getElementById('valor_contrato_atualizado');
        
        // Escutar evento do card amarelo
        document.addEventListener('valorMaisUmExercicioChange', function(event) {
            const shouldShow = event.detail.shouldShow;
            
            if (shouldShow) {
                // Mostrar com animação
                campoValorMaisUm.style.display = 'block';
                setTimeout(() => {
                    campoValorMaisUm.style.opacity = '1';
                    campoValorMaisUm.style.transform = 'translateY(0)';
                }, 10);
                
                // Tornar obrigatório
                inputValorMaisUm.setAttribute('required', 'required');
            } else {
                // Esconder com animação
                campoValorMaisUm.style.opacity = '0';
                campoValorMaisUm.style.transform = 'translateY(-20px)';
                
                setTimeout(() => {
                    campoValorMaisUm.style.display = 'none';
                }, 500);
                
                // Remover obrigatoriedade e limpar valor
                inputValorMaisUm.removeAttribute('required');
                inputValorMaisUm.value = '';
            }
        });
    });
    
    // Função aprimorada para mostrar/ocultar campo com efeito suave
    function toggleValorMaisUmExercicio(mostrar) {
        const campoValor = document.getElementById('valor-mais-um-exercicio');
        const justificativaValor = document.querySelector('.justificativa-valor');
        const cardCiano = document.querySelector('.card.bg-info');
        
        if (mostrar) {
            campoValor.style.display = 'block';
            campoValor.classList.remove('hide');
            campoValor.classList.add('show');
            
            // Adicionar margem suave aos elementos que se deslocam
            setTimeout(() => {
                if (justificativaValor) {
                    justificativaValor.style.marginTop = '1rem';
                }
                if (cardCiano) {
                    cardCiano.style.marginTop = '1rem';
                }
            }, 100);
            
            document.getElementById('valor_contrato_atualizado').required = true;
        } else {
            campoValor.classList.remove('show');
            campoValor.classList.add('hide');
            
            // Remover margem dos elementos que se deslocam
            if (justificativaValor) {
                justificativaValor.style.marginTop = '0';
            }
            if (cardCiano) {
                cardCiano.style.marginTop = '0';
            }
            
            setTimeout(() => {
                campoValor.style.display = 'none';
            }, 300);
            
            document.getElementById('valor_contrato_atualizado').required = false;
            document.getElementById('valor_contrato_atualizado').value = '';
        }
    }
    
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>