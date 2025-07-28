{{-- Seção 1: Informações do Item --}}
<div class="card card-outline card-primary">
    <div class="card-header bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-box me-2"></i>
            Informações do Item/Serviço
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-12 mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-tag text-primary me-1"></i>
                    Nome do Item <span class="text-danger">*</span>
                </label>
                <input type="text" name="nome_item" class="form-control form-control-lg @error('nome_item') is-invalid @enderror" required
                    value="{{ old('nome_item', $ppp->nome_item ?? '') }}"
                    placeholder="Ex: Aluguel de impressoras ou Consultoria para suporte em TI">
                @error('nome_item')
                    <div class="invalid-feedbackCriar novo PPP">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-lg-8 mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-cubes text-primary me-1"></i>
                    Quantidade <span class="text-danger">*</span>
                </label>
                <input type="text" name="quantidade" class="form-control form-control-lg @error('quantidade') is-invalid @enderror" required
                    value="{{ old('quantidade', $ppp->quantidade ?? '') }}"
                    placeholder="Ex: 2 unidades ou 1 visita/ano">
                @error('quantidade')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-lg-4 mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-exclamation-triangle text-primary me-1"></i>
                    Grau de prioridade <span class="text-danger">*</span>
                </label>
                <select class="form-control form-control-lg" name="grau_prioridade" required>
                    <option value="" disabled {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    <option value="Alta" {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == 'Alta' ? 'selected' : '' }}>🔴 Alta</option>
                    <option value="Média" {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == 'Média' ? 'selected' : '' }}>🟡 Média</option>
                    <option value="Baixa" {{ old('grau_prioridade', $ppp->grau_prioridade ?? '') == 'Baixa' ? 'selected' : '' }}>🟢 Baixa</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-file-alt text-primary me-1"></i>
                    Descrição sucinta do objeto <span class="text-danger">*</span>
                </label>
                <textarea name="descricao" class="form-control @error('descricao') is-invalid @enderror" rows="4" maxlength="500" required 
                    placeholder="Descreva detalhadamente o objeto da contratação">{{ old('descricao', $ppp->descricao ?? '') }}</textarea>
                @error('descricao')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-lg-4 mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-cog text-primary me-1"></i>
                    Natureza do Objeto <span class="text-danger">*</span>
                </label>
                <select class="form-control form-control-lg" name="natureza_objeto" required>
                    <option value="" disabled {{ old('natureza_objeto', $ppp->natureza_objeto ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    @foreach (['Serviço não continuado', 'Serviço continuado', 'Material de consumo', 'Bem permanente/ equipamento'] as $natureza)
                        <option value="{{ $natureza }}" {{ old('natureza_objeto', $ppp->natureza_objeto ?? '') == $natureza ? 'selected' : '' }}>{{ $natureza }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <label class="form-label fw-bold">
                    <i class="fas fa-clipboard-list text-primary me-1"></i>
                    Justificativa da necessidade <span class="text-danger">*</span>
                </label>
                <textarea name="justificativa_pedido" class="form-control @error('justificativa_pedido') is-invalid @enderror" rows="4" maxlength="1000" required 
                    placeholder="Justifique detalhadamente a necessidade desta contratação">{{ old('justificativa_pedido', $ppp->justificativa_pedido ?? '') }}</textarea>
                @error('justificativa_pedido')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-lg-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-list text-primary me-1"></i>
                    Categoria <span class="text-danger">*</span>
                </label>
                <select class="form-control form-control-lg" name="categoria" required>
                    <option value="" disabled {{ old('categoria', $ppp->categoria ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    @foreach (['Aquisição de bens', 'Contratação de Serviço', 'Obras', 'T.I.'] as $cat)
                        <option value="{{ $cat }}" {{ old('categoria', $ppp->categoria ?? '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if(!isset($ppp) || !$ppp->id)
            <div class="d-flex justify-content-end mt-3">
                <button type="button" id="btn-avancar-card-azul" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-right me-2"></i>
                    Avançar
                </button>
            </div>
        @endif
    </div>
</div>
