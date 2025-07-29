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
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-plus text-success me-1"></i>
                        Valor se +1 exercício <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="valor_contrato_atualizado" class="form-control form-control-lg money-field" required
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