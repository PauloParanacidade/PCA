{{-- Conteúdo do Modal de Histórico (usado via AJAX ou renderização direta) --}}
<div class="modal-header bg-light">
    <h5 class="modal-title">
        <i class="fas fa-history mr-2"></i>Histórico do PPP: {{ $ppp->nome_item }}
    </h5>
</div>

<div class="modal-body">
    @if($historicos && $historicos->count() > 0)
        <div class="timeline">
            @foreach($historicos as $item)
                <div class="timeline-item">
                    <div class="timeline-marker 
                        @switch($item->acao)
                            @case('rascunho_criado')
                                bg-secondary
                                @break
                            @case('ppp_enviado')
                                bg-warning
                                @break
                            @case('em_avaliacao')
                                bg-info
                                @break
                            @case('aprovacao_intermediaria')
                                bg-primary
                                @break
                            @case('aprovacao_final')
                                bg-success
                                @break
                            @case('reprovacao')
                                bg-danger
                                @break
                            @case('correcao_solicitada')
                                bg-orange
                                @break
                            @case('em_correcao')
                                bg-purple
                                @break
                            @default
                                bg-dark
                        @endswitch">
                        <i class="fas fa-circle" style="font-size: 8px;"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">
                            @switch($item->acao)
                                @case('rascunho_criado')
                                    <i class="fas fa-file-alt mr-1"></i>Rascunho criado por {{ $item->usuario->name ?? 'Sistema' }}
                                    @if($item->usuario && $item->usuario->setor)
                                        <small class="text-muted"> - {{ $item->usuario->setor }}</small>
                                    @endif
                                    @break
                                @case('ppp_enviado')
                                    <i class="fas fa-paper-plane mr-1"></i>PPP enviado para aprovação por {{ $item->usuario->name ?? 'Sistema' }}
                                    @if($item->usuario && $item->usuario->setor)
                                        <small class="text-muted"> - {{ $item->usuario->setor }}</small>
                                    @endif
                                    @break
                                @case('em_avaliacao')
                                    <i class="fas fa-search mr-1"></i>Em avaliação por {{ $item->usuario->name ?? 'Sistema' }}
                                    @if($item->usuario && $item->usuario->setor)
                                        <small class="text-muted"> - {{ $item->usuario->setor }}</small>
                                    @endif
                                    @break
                                @case('aprovacao_intermediaria')
                                    <i class="fas fa-check mr-1"></i>Aprovado por {{ $item->usuario->name ?? 'Sistema' }}
                                    @if($item->usuario && $item->usuario->setor)
                                        <small class="text-muted"> - {{ $item->usuario->setor }}</small>
                                    @endif
                                    @break
                                @case('aprovacao_final')
                                    <i class="fas fa-check-double mr-1"></i>Aprovação final por {{ $item->usuario->name ?? 'Sistema' }}
                                    @if($item->usuario && $item->usuario->setor)
                                        <small class="text-muted"> - {{ $item->usuario->setor }}</small>
                                    @endif
                                    @break
                                @case('reprovacao')
                                    <i class="fas fa-times mr-1"></i>Reprovado por {{ $item->usuario->name ?? 'Sistema' }}
                                    @if($item->usuario && $item->usuario->setor)
                                        <small class="text-muted"> - {{ $item->usuario->setor }}</small>
                                    @endif
                                    @break
                                @case('correcao_solicitada')
                                    <i class="fas fa-edit mr-1"></i>Correção solicitada por {{ $item->usuario->name ?? 'Sistema' }}
                                    @if($item->usuario && $item->usuario->setor)
                                        <small class="text-muted"> - {{ $item->usuario->setor }}</small>
                                    @endif
                                    @break
                                @case('em_correcao')
                                    <i class="fas fa-wrench mr-1"></i>Em correção por {{ $item->usuario->name ?? 'Sistema' }}
                                    @if($item->usuario && $item->usuario->setor)
                                        <small class="text-muted"> - {{ $item->usuario->setor }}</small>
                                    @endif
                                    @break
                                @default
                                    <i class="fas fa-info-circle mr-1"></i>{{ ucfirst(str_replace('_', ' ', $item->acao)) }} por {{ $item->usuario->name ?? 'Sistema' }}
                                    @if($item->usuario && $item->usuario->setor)
                                        <small class="text-muted"> - {{ $item->usuario->setor }}</small>
                                    @endif
                            @endswitch
                        </div>
                        @if($item->justificativa)
                            <div class="timeline-text">
                                <strong><i class="fas fa-comment mr-1"></i>Comentário:</strong> 
                                <span class="text-muted">{{ $item->justificativa }}</span>
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
            <h5 class="text-muted">Nenhum histórico encontrado</h5>
            <p class="text-muted mb-0">Este PPP ainda não possui registros de histórico.</p>
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
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    margin-left: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-title {
    margin: 0 0 8px 0;
    font-weight: 600;
    color: #495057;
    font-size: 1rem;
    line-height: 1.4;
}

.timeline-text {
    margin: 0 0 8px 0;
    color: #6c757d;
    line-height: 1.5;
    background: #fff;
    padding: 10px;
    border-radius: 4px;
    border-left: 3px solid #17a2b8;
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

/* Responsividade */
@media (max-width: 768px) {
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -15px;
        width: 24px;
        height: 24px;
    }
    
    .timeline-content {
        margin-left: 10px;
        padding: 12px;
    }
}
</style>