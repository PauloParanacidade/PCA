{{-- Seção 4: Vinculação/Dependência (Card Ciano) --}}
<div class="col-12 mb-4">
    <div class="card card-outline card-info">
        <div class="card-header bg-info">
            <h3 class="card-title text-white">
                <i class="fas fa-link me-2"></i>
                Vinculação/Dependência
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-question-circle text-info me-1"></i>
                        Possui vinculação/dependência? <span class="text-danger">*</span>
                    </label>
                    <select name="vinculacao_item" id="vinculacao_item" class="form-control form-control-lg" required>
                        <option value="" disabled {{ old('vinculacao_item', $ppp->vinculacao_item ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                        <option value="Sim" {{ old('vinculacao_item', $ppp->vinculacao_item ?? '') == 'Sim' ? 'selected' : '' }}>✅ Sim</option>
                        <option value="Não" {{ old('vinculacao_item', $ppp->vinculacao_item ?? '') == 'Não' ? 'selected' : '' }}>❌ Não</option>
                    </select>
                </div>
                <div class="col-lg-6">
                    <div id="campo_justificativa_vinculacao" style="display: none;">
                        <label class="form-label fw-bold">
                            <i class="fas fa-edit text-info me-1"></i>
                            Justificativa da vinculação
                        </label>
                        <textarea name="justificativa_vinculacao" id="justificativa_vinculacao" class="form-control" rows="4" maxlength="600"
                            placeholder="Identifique a qual item o pedido está vinculado e justifique a dependência">{{ old('justificativa_vinculacao', $ppp->justificativa_vinculacao ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <small>
                    <strong>Vinculação:</strong> Quando este item depende de outro para funcionar adequadamente.
                </small>
            </div>
        </div>
    </div>
</div>