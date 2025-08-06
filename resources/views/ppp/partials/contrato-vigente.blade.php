{{-- Seção 2: Contrato Vigente (Card Amarelo) --}}
<div class="col-12 mb-4">
    <div class="card card-outline card-warning h-100">
        <div class="card-header bg-warning">
            <h3 class="card-title text-dark">
                <i class="fas fa-file-contract me-2"></i>
                Contrato Vigente
            </h3>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-file-contract text-warning me-1"></i>
                    Objeto tem contrato vigente? <span class="text-danger">*</span>
                    <i class="fas fa-question-circle text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Verifique se já existe um contrato ativo para este mesmo objeto ou serviço na instituição"></i>
                </label>
                <select name="tem_contrato_vigente" id="tem_contrato_vigente" class="form-control form-control-lg" required>
                    <option value="" disabled {{ old('tem_contrato_vigente', $ppp->tem_contrato_vigente ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    <option value="Sim" {{ old('tem_contrato_vigente', $ppp->tem_contrato_vigente ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                    <option value="Não" {{ old('tem_contrato_vigente', $ppp->tem_contrato_vigente ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                </select>
            </div>
            
            {{-- Campos quando TEM contrato vigente --}}
            <div id="campos-contrato-vigente" class="mt-3" style="display: none;">
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-hashtag text-warning me-1"></i>
                        Número e Ano do contrato
                        <i class="fas fa-question-circle text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Informe o número e ano do contrato vigente separadamente"></i>
                    </label>
                    <div class="row align-items-start">
                        <div class="col-md-5">
                            <input type="number" name="num_contrato" id="num_contrato" class="form-control contract-number"
                            value="{{ old('num_contrato', $ppp->num_contrato ?? '') }}" 
                            placeholder="1" min="1" max="9999">
                            <small class="form-text text-muted">Número do contrato</small>
                        </div>
                        
                        <div class="col-md-1 d-flex justify-content-center">
                            <span class="align-self-end" style="font-size: 2rem; line-height: 1.2; padding-bottom: .25rem;">/</span>
                        </div>
                        
                        <div class="col-md-6">
                            <input type="text" 
                            name="ano_contrato"
                            id="ano_contrato" 
                            class="form-control" 
                            placeholder="Ano (ex: 2024)" 
                            value="{{ old('ano_contrato', ($isCardAmarelo ?? false) ? '' : ($ppp->ano_contrato ?? '')) }}"
                            data-default-value="{{ date('Y') - 1 }}">
                            <small class="form-text text-muted">Ano da criação do contrato</small>
                        </div>
                        
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-times text-warning me-1"></i>
                        Mês e Ano da vigência final prevista
                        <i class="fas fa-question-circle text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Selecione o mês e ano em que o contrato atual está previsto para terminar"></i>
                    </label>
                    <div class="row">
                        <div class="col-md-6">
                            <select name="mes_vigencia_final" id="mes_vigencia_final" class="form-control">
                                <option value="" disabled {{ old('mes_vigencia_final', $ppp->mes_vigencia_final ?? '') == '' ? 'selected' : '' }}>Selecione o mês</option>
                                @php
                                $meses = [
                                '01' => 'Janeiro',
                                '02' => 'Fevereiro', 
                                '03' => 'Março',
                                '04' => 'Abril',
                                '05' => 'Maio',
                                '06' => 'Junho',
                                '07' => 'Julho',
                                '08' => 'Agosto',
                                '09' => 'Setembro',
                                '10' => 'Outubro',
                                '11' => 'Novembro',
                                '12' => 'Dezembro'
                                ];
                                @endphp
                                @foreach($meses as $numero => $nome)
                                <option value="{{ $numero }}" {{ old('mes_vigencia_final', $ppp->mes_vigencia_final ?? '') == $numero ? 'selected' : '' }}>{{ $nome }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Mês de vigência final</small>
                        </div>
                        <div class="col-md-6">
                            <input type="text"
                            name="ano_vigencia_final"
                            id="ano_vigencia_final"
                            class="form-control"
                            placeholder="Ano (ex: 2026)"
                            value="{{ old('ano_vigencia_final', $isCardAmarelo ? '' : ($ppp->ano_vigencia_final ?? date('Y') + 1)) }}">
                            <small class="form-text text-muted">Ano de vigência final</small>
                        </div>
                    </div>
                </div>
                
                {{-- Campo Prorrogável - só aparece se ano final = ano PCA --}}
                <div id="campo-prorrogavel" class="mb-3" style="display: none; opacity: 0; transform: translateY(-20px); transition: all 0.5s ease-in-out;">
                    <label class="form-label fw-bold">
                        <i class="fas fa-sync-alt text-warning me-1"></i>
                        Prorrogável? <span class="text-danger">*</span>
                        <i class="fas fa-question-circle text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Verifique se o contrato atual permite prorrogação conforme previsto no instrumento contratual"></i>
                    </label>
                    <select name="contrato_prorrogavel" id="contrato_prorrogavel" class="form-control">
                        <option value="" disabled {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                        <option value="Sim" {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                        <option value="Não" {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                    </select>
                </div>
                
                {{-- Campo Irá Prorrogar - só aparece se Prorrogável = Sim --}}
                <div id="campo-pretensao-prorrogacao" class="mb-3" style="display: none; opacity: 0; transform: translateY(-20px); transition: all 0.5s ease-in-out;">
                    <label class="form-label fw-bold">
                        <i class="fas fa-handshake text-warning me-1"></i>
                        Irá prorrogar? <span class="text-danger">*</span>
                        <i class="fas fa-question-circle text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Informe se há intenção de prorrogar o contrato atual ou se será necessária nova contratação"></i>
                    </label>
                    <select name="renov_contrato" id="renov_contrato" class="form-control">
                        <option value="" disabled {{ old('renov_contrato', $ppp->renov_contrato ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                        <option value="Sim" {{ old('renov_contrato', $ppp->renov_contrato ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                        <option value="Não" {{ old('renov_contrato', $ppp->renov_contrato ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                    </select>
                </div>
            </div>
            
            {{-- Campos quando NÃO TEM contrato vigente --}}
            <div id="campos-sem-contrato" class="mt-3" style="display: none;">
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-plus text-warning me-1"></i>
                        Usuário irá informar o mês pretendido para início da prestação de serviço ou do fornecimento do bem <span class="text-danger">*</span>
                        <i class="fas fa-question-circle text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Selecione o mês em que você pretende que o serviço ou fornecimento tenha início, considerando os prazos de licitação"></i>
                    </label>
                    <div class="row">
                        <div class="col-md-6">
                            <select name="mes_inicio_prestacao" id="mes_inicio_prestacao" class="form-control">
                                <option value="" disabled {{ old('mes_inicio_prestacao', $ppp->mes_inicio_prestacao ?? '') == '' ? 'selected' : '' }}>Selecione o mês</option>
                                @php
                                $meses = [
                                '01' => 'Janeiro',
                                '02' => 'Fevereiro', 
                                '03' => 'Março',
                                '04' => 'Abril',
                                '05' => 'Maio',
                                '06' => 'Junho',
                                '07' => 'Julho',
                                '08' => 'Agosto',
                                '09' => 'Setembro',
                                '10' => 'Outubro',
                                '11' => 'Novembro',
                                '12' => 'Dezembro'
                                ];
                                @endphp
                                @foreach($meses as $numero => $nome)
                                <option value="{{ $numero }}" {{ old('mes_inicio_prestacao', $ppp->mes_inicio_prestacao ?? '') == $numero ? 'selected' : '' }}>{{ $nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" value="de {{ now()->year + 1 }}" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label fw-bold pe-4 flex-grow-1 mb-0">
                            <i class="fas fa-calendar-check text-warning me-1"></i>
                            Este novo objeto será um contrato a ser executado por mais de 1 exercício? <span class="text-danger">*</span>
                            <i class="fas fa-question-circle text-muted ms-1"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Informe se o contrato terá duração superior a um ano fiscal (exercício)">
                        </i>
                    </label>
                    
                    <select name="contrato_mais_um_exercicio" id="contrato_mais_um_exercicio" class="form-control" style="max-width: 140px; height: calc(1.5em + .75rem + 2px); font-size: 1rem;">
                        <option value="" disabled {{ old('contrato_mais_um_exercicio', $ppp->contrato_mais_um_exercicio ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                        <option value="Sim" {{ old('contrato_mais_um_exercicio', $ppp->contrato_mais_um_exercicio ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                        <option value="Não" {{ old('contrato_mais_um_exercicio', $ppp->contrato_mais_um_exercicio ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                    </select>
                </div>
            </div>
            
        </div>
    </div>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectContrato = document.getElementById('tem_contrato_vigente');
        const camposContrato = document.getElementById('campos-contrato-vigente');
        const camposSemContrato = document.getElementById('campos-sem-contrato');
        const campoProrrogavel = document.getElementById('campo-prorrogavel');
        const campoPretensao = document.getElementById('campo-pretensao-prorrogacao');
        const selectProrrogavel = document.getElementById('contrato_prorrogavel');
        const renovContratoSelect = document.getElementById('renov_contrato');
        const anoVigenciaInput = document.querySelector('input[name="ano_vigencia_final"]');
        const contratoMaisExercicioSelect = document.getElementById('contrato_mais_um_exercicio');
        const numContratoInput = document.getElementById('num_contrato');
        const anoContratoInput = document.getElementById('ano_contrato');
        
        // Máscara para número do contrato (formato 0001) - COMENTADO TEMPORARIAMENTE
        if (numContratoInput) {
            // TODO: Implementar máscara futuramente
            // REMOVER o event listener keydown que bloqueia a digitação
            // numContratoInput.addEventListener('keydown', function(e) {
            //     const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];
            //     if (allowedKeys.includes(e.key)) {
            //         return;
            //     }
            //     if (/[0-9]/.test(e.key) && e.target.value.length < 4) {
            //         return;
            //     }
            //     e.preventDefault();
            // });
            
            // Permitir digitação livre, mas formatar ao sair do campo (blur)
            // numContratoInput.addEventListener('blur', function(e) {
            //     let value = e.target.value.replace(/\D/g, ''); // Remove não-numéricos
            //     if (value.length > 0) {
            //         // Limita a 4 dígitos e completa com zeros à esquerda
            //         value = value.substring(0, 4).padStart(4, '0');
            //         e.target.value = value;
            //     }
            // });
            
            // Opcional: limitar a 4 caracteres durante a digitação
            // numContratoInput.addEventListener('input', function(e) {
            //     let value = e.target.value;
            //     if (value.length > 4) {
            //         e.target.value = value.substring(0, 4);
            //     }
            // });
        }
        
        // Comportamento do ano de vigência final
        if (anoVigenciaInput) {
            // Permitir apenas números (validação será feita no backend)
            anoVigenciaInput.addEventListener('input', function(e) {
                // Remover caracteres não numéricos
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                // Limitar a 4 dígitos
                if (e.target.value.length > 4) {
                    e.target.value = e.target.value.slice(0, 4);
                }
            });
        }
        
        // Comportamento do ano do contrato
        if (anoContratoInput) {
            // Controlar comportamento ao focar no campo
            anoContratoInput.addEventListener('focus', function(e) {
                if (e.target.value === '' && e.target.dataset.defaultValue) {
                    e.target.value = e.target.dataset.defaultValue;
                }
            });
            
            // Permitir apenas números (validação será feita no backend)
            anoContratoInput.addEventListener('input', function(e) {
                // Remover caracteres não numéricos
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                // Limitar a 4 dígitos
                if (e.target.value.length > 4) {
                    e.target.value = e.target.value.slice(0, 4);
                }
            });
        }
        
        // Função principal para alternar campos baseado na resposta "tem contrato vigente"
        function toggleCamposContrato() {
            const valor = selectContrato.value;
            
            if (valor === 'Sim') {
                // Mostrar apenas campos do contrato vigente
                camposContrato.style.display = 'block';
                camposSemContrato.style.display = 'none';
                
                // Limpar campos do "sem contrato"
                clearFieldsAndRequirements(camposSemContrato);
                
                // Tornar campos obrigatórios
                document.getElementById('num_contrato').setAttribute('required', 'required');
                document.getElementById('ano_contrato').setAttribute('required', 'required');
                document.getElementById('mes_vigencia_final').setAttribute('required', 'required');
                document.getElementById('ano_vigencia_final').setAttribute('required', 'required');
                
                // Verificar se deve mostrar campo prorrogável
                checkMostrarProrrogavel();
                
            } else if (valor === 'Não') {
                // Mostrar campos para quando não tem contrato
                camposContrato.style.display = 'none';
                camposSemContrato.style.display = 'block';
                
                // Limpar campos do contrato vigente
                clearFieldsAndRequirements(camposContrato);
                
                // Esconder campos condicionais do contrato vigente
                campoProrrogavel.style.display = 'none';
                campoPretensao.style.display = 'none';
                
                // Tornar campos obrigatórios
                document.getElementById('mes_inicio_prestacao').setAttribute('required', 'required');
                contratoMaisExercicioSelect.setAttribute('required', 'required');
                
            } else {
                // Esconder ambos se nenhuma opção selecionada
                camposContrato.style.display = 'none';
                camposSemContrato.style.display = 'none';
                campoProrrogavel.style.display = 'none';
                campoPretensao.style.display = 'none';
            }
            
            checkValorMaisUmExercicio();
        }
        
        // Função para verificar se deve mostrar campo "Prorrogável" - CORRIGIDA
        function checkMostrarProrrogavel() {
            const anoVigencia = anoVigenciaInput ? parseInt(anoVigenciaInput.value) : null;
            const anoPCA = new Date().getFullYear() + 1; // Ano do PCA é sempre ano atual + 1
            
            if (anoVigencia === anoPCA) {
                // Ano final = ano PCA, mostrar campo prorrogável com animação
                campoProrrogavel.style.display = 'block';
                setTimeout(() => {
                    campoProrrogavel.style.opacity = '1';
                    campoProrrogavel.style.transform = 'translateY(0)';
                }, 10);
                selectProrrogavel.setAttribute('required', 'required');
            } else {
                // Ano final ≠ ano PCA, esconder campos prorrogável e pretensão com animação
                campoProrrogavel.style.opacity = '0';
                campoProrrogavel.style.transform = 'translateY(-20px)';
                campoPretensao.style.opacity = '0';
                campoPretensao.style.transform = 'translateY(-20px)';
                
                setTimeout(() => {
                    campoProrrogavel.style.display = 'none';
                    campoPretensao.style.display = 'none';
                }, 500);
                
                selectProrrogavel.removeAttribute('required');
                renovContratoSelect.removeAttribute('required');
                selectProrrogavel.value = '';
                renovContratoSelect.value = '';
            }
            
            checkValorMaisUmExercicio();
        }
        
        // Função para alternar campo de pretensão de prorrogação - CORRIGIDA
        function toggleCampoPretensao() {
            const valor = selectProrrogavel.value;
            
            if (valor === 'Sim') {
                campoPretensao.style.display = 'block';
                setTimeout(() => {
                    campoPretensao.style.opacity = '1';
                    campoPretensao.style.transform = 'translateY(0)';
                }, 10);
                renovContratoSelect.setAttribute('required', 'required');
            } else if (valor === 'Não') {
                // Se prorrogável = Não, finalizar perguntas e ocultar campo valor com animação
                campoPretensao.style.opacity = '0';
                campoPretensao.style.transform = 'translateY(-20px)';
                
                setTimeout(() => {
                    campoPretensao.style.display = 'none';
                }, 500);
                
                renovContratoSelect.removeAttribute('required');
                renovContratoSelect.value = '';
            }
            
            checkValorMaisUmExercicio();
        }
        
        // Função para limpar campos e remover obrigatoriedade
        function clearFieldsAndRequirements(container) {
            const fields = container.querySelectorAll('input, select');
            fields.forEach(field => {
                field.removeAttribute('required');
                field.value = '';
            });
        }
        
        // Função para verificar se deve mostrar o campo "Valor Total até o final do contrato" - CORRIGIDA
        function checkValorMaisUmExercicio() {
            const temContrato = selectContrato.value;
            let shouldShow = false;
            
            if (temContrato === 'Não') {
                // Se não tem contrato, verificar se será executado por mais de 1 exercício
                const maisUmExercicio = contratoMaisExercicioSelect.value;
                shouldShow = (maisUmExercicio === 'Sim');
                
            } else if (temContrato === 'Sim') {
                // Se tem contrato, verificar toda a lógica conforme instruções
                const anoVigencia = anoVigenciaInput ? parseInt(anoVigenciaInput.value) : null;
                const anoPCA = new Date().getFullYear() + 1; // Ano do PCA
                
                // Se ano final do contrato é o mesmo ano do PCA
                if (anoVigencia === anoPCA) {
                    const prorrogavel = selectProrrogavel.value;
                    
                    if (prorrogavel === 'Sim') {
                        const vaiProrrogar = renovContratoSelect.value;
                        // Só mostrar se irá prorrogar = Sim
                        shouldShow = (vaiProrrogar === 'Sim');
                    } else if (prorrogavel === 'Não') {
                        // Se prorrogável = Não, campo fica oculto
                        shouldShow = false;
                    }
                } else {
                    // Se ano final ≠ ano PCA, campo fica oculto
                    shouldShow = false;
                }
            }
            
            // Disparar evento customizado para o card verde
            const event = new CustomEvent('valorMaisUmExercicioChange', {
                detail: { shouldShow: shouldShow }
            });
            document.dispatchEvent(event);
        }
        
        // Event listeners
        if (selectContrato) {
            selectContrato.addEventListener('change', toggleCamposContrato);
        }
        
        if (anoVigenciaInput) {
            anoVigenciaInput.addEventListener('change', function() {
                checkMostrarProrrogavel();
            });
        }
        
        if (selectProrrogavel) {
            selectProrrogavel.addEventListener('change', toggleCampoPretensao);
        }
        
        if (renovContratoSelect) {
            renovContratoSelect.addEventListener('change', checkValorMaisUmExercicio);
        }
        
        if (contratoMaisExercicioSelect) {
            contratoMaisExercicioSelect.addEventListener('change', checkValorMaisUmExercicio);
        }
        
        // Inicializar estados baseado nos valores atuais
        toggleCamposContrato();
        if (selectContrato.value === 'Sim' && anoVigenciaInput && anoVigenciaInput.value) {
            checkMostrarProrrogavel();
            if (selectProrrogavel.value) {
                toggleCampoPretensao();
            }
        }
        checkValorMaisUmExercicio();
    });
</script>