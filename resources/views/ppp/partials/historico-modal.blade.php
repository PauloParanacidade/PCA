<div class="modal-header">
    <h4 class="modal-title">Histórico do PPP {{ $ppp->nome_item }}</h4>
    <button type="button" class="close" data-dismiss="modal">
        <span>&times;</span>
    </button>
</div>
<div class="modal-body">
    @if($historicos && $historicos->count() > 0)
        <div class="timeline">
            @foreach($historicos as $historico)
                <div class="timeline-item">
                    <div class="timeline-marker 
                        @if($historico->status_atual == 1) bg-secondary
                        @elseif($historico->status_atual == 2) bg-info  
                        @elseif($historico->status_atual == 3) bg-warning
                        @elseif($historico->status_atual == 4) bg-orange
                        @elseif($historico->status_atual == 5) bg-purple
                        @elseif($historico->status_atual == 6) bg-success
                        @elseif($historico->status_atual == 7) bg-danger
                        @else bg-dark
                        @endif"></div>
                    <div class="timeline-content">
                        <h6 class="timeline-title">
                            @if($historico->status_anterior)
                                @if($historico->status_atual == 2)
                                    📤 Enviado para Aprovação
                                @elseif($historico->status_atual == 3)
                                    👁️ Em Avaliação
                                @elseif($historico->status_atual == 4)
                                    ⚠️ Solicitada Correção
                                @elseif($historico->status_atual == 5)
                                    ✏️ Em Correção
                                @elseif($historico->status_atual == 6)
                                    ✅ Aprovado Final
                                @elseif($historico->status_atual == 7)
                                    ❌ Cancelado
                                @else
                                    🔄 Status Alterado
                                @endif
                            @else
                                📝 PPP Criado (Rascunho)
                            @endif
                        </h6>
                        <p class="timeline-text">
                            @if($historico->status_anterior)
                                <strong>De:</strong> {{ $historico->statusAnterior->nome ?? 'Status anterior' }}<br>
                                <strong>Para:</strong> {{ $historico->statusAtual->nome ?? 'Status atual' }}
                            @else
                                PPP foi criado com sucesso.
                            @endif
                        </p>
                        @if($historico->justificativa)
                            <p class="timeline-text"><strong>Justificativa:</strong> {{ $historico->justificativa }}</p>
                        @endif
                        <small class="text-muted">
                            {{ $historico->created_at->format('d/m/Y H:i') }}
                            @if($historico->usuario)
                                - por {{ $historico->usuario->name }}
                            @endif
                        </small>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-4">
            <i class="fas fa-info-circle text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3 text-muted">Nenhum histórico encontrado</h5>
            <p class="text-muted">O histórico será exibido conforme as ações forem realizadas no PPP.</p>
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i>Fechar
    </button>
</div>