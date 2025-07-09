{{-- Seção 3: Informações Financeiras (Card Verde) --}}
<div class="col-lg-6 mb-4">
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
                    <input type="text" name="estimativa_valor" class="form-control form-control-lg estimativa_valor money-field protocolDisplayMask" required
                        value="{{ old('estimativa_valor', $ppp->estimativa_valor ?? '') }}" 
                        placeholder="R$ 0,00" autocomplete="off">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-university text-success me-1"></i>
                        Origem do recurso <span class="text-danger">*</span>
                    </label>
                    <select class="form-control form-control-lg" name="origem_recurso" required>
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
                    <input type="text" name="valor_contrato_atualizado" class="form-control form-control-lg valor_contrato_atualizado money-field protocolDisplayMask" required
                        value="{{ old('valor_contrato_atualizado', $ppp->valor_contrato_atualizado ?? '') }}"
                        placeholder="R$ 0,00" autocomplete="off">
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calculator text-success me-1"></i>
                        Justificativa do valor estimado <span class="text-danger">*</span>
                    </label>
                    <textarea name="justificativa_valor" class="form-control @error('justificativa_valor') is-invalid @enderror" rows="3" maxlength="800" required
                        placeholder="Ex: Cotação realizada em 01/01/2025 no portal gov. Índice de aumento x% conforme indicador y">{{ old('justificativa_valor', $ppp->justificativa_valor ?? '') }}</textarea>
                    @error('justificativa_valor')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>