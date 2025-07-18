<div class="modal-header bg-light">
    <h5 class="modal-title">
        <i class="fas fa-history mr-2"></i>Histórico do PPP: {{ $ppp->nome_item }}
    </h5>
</div>

<div class="modal-body">
    @if($historico && $historico->count() > 0)
        <div class="timeline">
            @foreach($historico as $item)
                <div class="timeline-item">
                    <div class="timeline-marker 
                        @if($item->status_anterior === 'rascunho') bg-secondary
                        @elseif($item->status_anterior === 'aguardando_aprovacao') bg-warning
                        @elseif($item->status_anterior === 'em_avaliacao') bg-info
                        @elseif($item->status_anterior === 'aprovado_final') bg-success
                        @elseif($item->status_anterior === 'cancelado') bg-danger
                        @else bg-dark
                        @endif">
                        <i class="fas fa-circle" style="font-size: 8px;"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">
                            @if($item->status_anterior === 'rascunho' && $item->status_novo === 'aguardando_aprovacao')
                                Rascunho criado por {{ $item->usuario->name ?? 'Sistema' }} - {{ $item->usuario->setor ?? 'N/A' }}
                            @else
                                {{ ucfirst(str_replace('_', ' ', $item->status_novo)) }}
                            @endif
                        </div>
                        @if($item->comentario)
                            <div class="timeline-text">
                                <strong>Comentário:</strong> {{ $item->comentario }}
                            </div>
                        @endif
                        <div class="timeline-date">
                            <small class="text-muted">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $item->created_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-4">
            <i class="fas fa-history fa-3x text-muted mb-3"></i>
            <p class="text-muted">Nenhum histórico encontrado para este PPP.</p>
        </div>
    @endif
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    margin-left: 15px;
}

.timeline-title {
    margin: 0 0 8px 0;
    font-weight: 600;
    color: #495057;
    font-size: 1rem;
}

.timeline-text {
    margin: 0 0 8px 0;
    color: #6c757d;
    line-height: 1.5;
}

.timeline-date {
    margin: 0;
}

/* Cores específicas para diferentes status */
.bg-orange {
    background-color: #fd7e14 !important;
}

.bg-purple {
    background-color: #6f42c1 !important;
}
</style>