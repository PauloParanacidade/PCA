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
                    <i class="fas fa-question-circle text-warning me-1"></i>
                    Objeto tem contrato vigente? <span class="text-danger">*</span>
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
                        Número/Ano do contrato
                    </label>
                    <input type="text" name="num_contrato" id="num_contrato" class="form-control contract-number"
                        value="{{ old('num_contrato', $ppp->num_contrato ?? '') }}" 
                        placeholder="Ex: 0001/2023" autocomplete="off">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-times text-warning me-1"></i>
                        Mês da vigência final prevista
                    </label>
                    <input type="month" name="mes_vigencia_final" class="form-control"
                        value="{{ old('mes_vigencia_final', $ppp->mes_vigencia_final ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-sync-alt text-warning me-1"></i>
                        Prorrogável <span class="text-danger">*</span>
                    </label>
                    <select name="contrato_prorrogavel" id="contrato_prorrogavel" class="form-control">
                        <option value="" disabled {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                        <option value="Sim" {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                        <option value="Não" {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                    </select>
                </div>

                <div id="campo-pretensao-prorrogacao" class="mb-3" style="display: none;">
                    <label class="form-label fw-bold">
                        <i class="fas fa-handshake text-warning me-1"></i>
                        Pretensão de prorrogação <span class="text-danger">*</span>
                    </label>
                    <select name="renov_contrato" id="renov_contrato" class="form-control">
                        <option value="" disabled {{ old('renov_contrato', $ppp->renov_contrato ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                        <option value="Sim" {{ old('renov_contrato', $ppp->renov_contrato ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                        <option value="Não" {{ old('renov_contrato', $ppp->renov_contrato ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                    </select>
                </div>
            </div>

            {{-- NOVO: Campo quando NÃO TEM contrato vigente --}}
            <div id="campo-mes-inicio-prestacao" class="mt-3" style="display: none;">
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-plus text-warning me-1"></i>
                        Mês pretendido para início da prestação de serviço ou do fornecimento do bem <span class="text-danger">*</span>
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
                            <input type="text" class="form-control" value="de 2026" readonly style="background-color: #f8f9fa;">
                        </div>
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
    const campoMesInicio = document.getElementById('campo-mes-inicio-prestacao'); // NOVO
    const selectProrrogavel = document.getElementById('contrato_prorrogavel');
    const campoPretensao = document.getElementById('campo-pretensao-prorrogacao');
    
    if (selectContrato) {
        selectContrato.addEventListener('change', toggleCamposContrato);
        // Verificar estado inicial
        toggleCamposContrato();
    }
    
    function toggleCamposContrato() {
        if (selectContrato.value === 'Sim') {
            camposContrato.style.display = 'block';
            campoMesInicio.style.display = 'none'; // NOVO: Esconder campo de mês início
            // Remover obrigatoriedade do campo mês início
            document.getElementById('mes_inicio_prestacao').removeAttribute('required');
            // Verificar se já existe valor selecionado para prorrogável
            toggleCampoPretensao();
        } else if (selectContrato.value === 'Não') {
            camposContrato.style.display = 'none';
            campoMesInicio.style.display = 'block'; // NOVO: Mostrar campo de mês início
            campoPretensao.style.display = 'none';
            // Tornar o campo mês início obrigatório
            document.getElementById('mes_inicio_prestacao').setAttribute('required', 'required');
        } else {
            camposContrato.style.display = 'none';
            campoMesInicio.style.display = 'none'; // NOVO: Esconder ambos quando nada selecionado
            campoPretensao.style.display = 'none';
            // Remover obrigatoriedade
            document.getElementById('mes_inicio_prestacao').removeAttribute('required');
        }
    }
    
    function toggleCampoPretensao() {
        if (selectProrrogavel && selectProrrogavel.value === 'Sim') {
            campoPretensao.style.display = 'block';
            // Tornar o campo obrigatório
            document.getElementById('renov_contrato').setAttribute('required', 'required');
        } else {
            campoPretensao.style.display = 'none';
            // Remover obrigatoriedade
            document.getElementById('renov_contrato').removeAttribute('required');
            // Limpar valor
            document.getElementById('renov_contrato').value = '';
        }
    }
    
    if (selectProrrogavel) {
        selectProrrogavel.addEventListener('change', toggleCampoPretensao);
    }
});
</script>