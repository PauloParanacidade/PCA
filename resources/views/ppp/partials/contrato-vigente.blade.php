{{-- Seção 2: Contrato Vigente (Card Amarelo) --}}
<div class="col-lg-6 mb-4">
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
                    <option value="Sim" {{ old('tem_contrato_vigente', $ppp->tem_contrato_vigente ?? '') == 'Sim' ? 'selected' : '' }}>✅ Sim</option>
                    <option value="Não" {{ old('tem_contrato_vigente', $ppp->tem_contrato_vigente ?? '') == 'Não' ? 'selected' : '' }}>❌ Não</option>
                </select>
            </div>

            <div id="campos_contrato" style="display: none;">
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

                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold">Prorrogável?</label>
                        <select name="contrato_prorrogavel" id="contrato_prorrogavel" class="form-control">
                            <option value="" disabled {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                            <option value="Sim" {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                            <option value="Não" {{ old('contrato_prorrogavel', $ppp->contrato_prorrogavel ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                        </select>
                    </div>
                    <div class="col-6 mb-3" id="campo_pretensao_prorrogacao" style="display: none;">
                        <label class="form-label fw-bold">Pretensão de prorrogação?</label>
                        <select name="renov_contrato" id="renov_contrato" class="form-control">
                            <option value="" disabled {{ old('renov_contrato', $ppp->renov_contrato ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                            <option value="Sim" {{ old('renov_contrato', $ppp->renov_contrato ?? '') == 'Sim' ? 'selected' : '' }}>Sim</option>
                            <option value="Não" {{ old('renov_contrato', $ppp->renov_contrato ?? '') == 'Não' ? 'selected' : '' }}>Não</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>