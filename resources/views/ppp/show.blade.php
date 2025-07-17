@extends('layouts.adminlte-custom')

@section('title', 'Visualizar PPP')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-file-contract text-primary mr-2"></i>
                        Visualizar PPP
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('ppp.index') }}">PPPs</a></li>
                        <li class="breadcrumb-item active">Visualizar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Conteúdo Principal -->
        <div class="col-lg-9">
            <!-- Header do PPP -->
            <div class="card bg-gradient-primary shadow-lg mb-3">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="text-white mb-1 font-weight-bold">{{ $ppp->nome_item }}</h2>
                            <small class="text-white-50 d-block mt-1">
                                Criado em {{ $ppp->created_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-light badge-lg">
                                #{{ $ppp->id }}
                            </span>
                            <div class="mt-1">
                                <span class="badge 
                                    @if($ppp->status_fluxo === 'rascunho') badge-secondary
                                    @elseif($ppp->status_fluxo === 'aguardando_aprovacao') badge-warning
                                    @elseif($ppp->status_fluxo === 'em_avaliacao') badge-info
                                    @elseif($ppp->status_fluxo === 'aprovado_final') badge-success
                                    @elseif($ppp->status_fluxo === 'cancelado') badge-danger
                                    @else badge-dark
                                    @endif badge-lg">
                                    {{ ucfirst(str_replace('_', ' ', $ppp->status_fluxo)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRIMEIRA LINHA -->
            <div class="row mb-3">
                <!-- Card 1: Informações do Item/Serviço (AZUL) -->
                <div class="col-lg-6">
                    <div class="card card-outline card-primary shadow-sm h-100">
                        <div class="card-header bg-primary py-2">
                            <h3 class="card-title text-white mb-0">
                                <i class="fas fa-box mr-2"></i>
                                Informações do Item/Serviço
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <!-- Primeira linha: Nome do Item e Categoria -->
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <label class="info-label">Nome do Item</label>
                                        <div class="info-value font-weight-bold">{{ $ppp->nome_item }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <label class="info-label">Categoria</label>
                                        <div class="info-value">
                                            <span class="badge badge-primary badge-lg">
                                                {{ $ppp->categoria ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Segunda linha: Quantidade e Prioridade -->
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <label class="info-label">Quantidade</label>
                                        <div class="info-value">{{ $ppp->quantidade ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <label class="info-label">Prioridade</label>
                                        <div class="info-value">
                                            @if($ppp->grau_prioridade)
                                                <span class="badge badge-lg 
                                                    @if($ppp->grau_prioridade === 'Alta') badge-danger
                                                    @elseif($ppp->grau_prioridade === 'Média') badge-warning
                                                    @else badge-secondary
                                                    @endif">
                                                    {{ $ppp->grau_prioridade }}
                                                </span>
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="info-group">
                                <label class="info-label">Descrição/Especificação</label>
                                <div class="info-value-text">
                                    {{ $ppp->descricao ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Contrato Vigente (AMARELO) -->
                <div class="col-lg-6">
                    <div class="card card-outline card-warning shadow-sm h-100">
                        <div class="card-header bg-warning py-2">
                            <h3 class="card-title text-white mb-0">
                                <i class="fas fa-file-contract mr-2"></i>
                                Contrato Vigente
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <div class="info-group mb-2">
                                <label class="info-label">Possui Contrato Vigente</label>
                                <div class="info-value">
                                    <span class="badge {{ $ppp->tem_contrato_vigente === 'Sim' ? 'badge-success' : 'badge-secondary' }} badge-lg">
                                        {{ $ppp->tem_contrato_vigente ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            
                            @if($ppp->tem_contrato_vigente === 'Sim')
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="info-label">Número/Ano do contrato</label>
                                            <div class="info-value font-weight-bold">{{ $ppp->num_contrato ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="info-label">Mês da vigência final prevista</label>
                                            <div class="info-value">
                                                {{ $ppp->mes_vigencia_final ? \Carbon\Carbon::parse($ppp->mes_vigencia_final . '-01')->format('m/Y') : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                                                    <div class="row mb-2">
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="info-label">Prorrogável</label>
                                            <div class="info-value">
                                                <span class="badge {{ $ppp->contrato_prorrogavel === 'Sim' ? 'badge-info' : 'badge-secondary' }}">
                                                    {{ $ppp->contrato_prorrogavel ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="info-label">Pretensão de Prorrogação</label>
                                            <div class="info-value">
                                                <span class="badge {{ $ppp->renov_contrato === 'Sim' ? 'badge-success' : 'badge-secondary' }}">
                                                    {{ $ppp->renov_contrato ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-2">
                                    <i class="fas fa-file-times text-muted" style="font-size: 2.5rem;"></i>
                                    <p class="text-muted mt-2 mb-0">Sem contrato vigente</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEGUNDA LINHA -->
            <div class="row mb-3">
                <!-- Card 3: Informações Financeiras (VERDE) -->
                <div class="col-lg-6">
                    <div class="card card-outline card-success shadow-sm h-100">
                        <div class="card-header bg-success py-2">
                            <h3 class="card-title text-white mb-0">
                                <i class="fas fa-dollar-sign mr-2"></i>
                                Informações Financeiras
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <label class="info-label">Valor total estimado (exercício)</label>
                                        <div class="info-value font-weight-bold text-success">
                                            R$ {{ number_format($ppp->estimativa_valor ?? 0, 2, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <label class="info-label">Origem do Recurso</label>
                                        <div class="info-value">
                                            <span class="badge badge-success badge-lg">
                                                {{ $ppp->origem_recurso ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <label class="info-label">Valor se +1 exercício</label>
                                        <div class="info-value font-weight-bold text-success">
                                            R$ {{ number_format($ppp->valor_contrato_atualizado ?? 0, 2, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="info-group">
                                <label class="info-label">Justificativa do Valor Estimado</label>
                                <div class="info-value-text">
                                    {{ $ppp->justificativa_valor ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Vinculação/Dependência (CIANO) -->
                <div class="col-lg-6">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-header bg-info py-2">
                            <h3 class="card-title text-white mb-0">
                                <i class="fas fa-link mr-2"></i>
                                Vinculação/Dependência
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <div class="info-group mb-2">
                                <label class="info-label">Possui Vinculação/Dependência</label>
                                <div class="info-value">
                                    <span class="badge {{ $ppp->vinculacao_item === 'Sim' ? 'badge-info' : 'badge-secondary' }} badge-lg">
                                        {{ $ppp->vinculacao_item ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            @if($ppp->vinculacao_item === 'Sim')
                                <div class="info-group">
                                    <label class="info-label">Descrição da Vinculação</label>
                                    <div class="info-value-text">
                                        {{ $ppp->justificativa_vinculacao ?? 'N/A' }}
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-2">
                                    <i class="fas fa-unlink text-muted" style="font-size: 2.5rem;"></i>
                                    <p class="text-muted mt-2 mb-0">Sem vinculação ou dependência</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar com Ações -->
        <div class="col-lg-3">
            <!-- Card de Ações (ROXO) -->
    <div class="card card-outline card-purple shadow-sm">
    <div class="card-header bg-purple py-2">
        <h3 class="card-title text-white mb-0">
            <i class="fas fa-cogs mr-2"></i>
            Ações
        </h3>
    </div>
    <div class="card-body py-3">
        <div class="d-flex flex-column">
            <button type="button" class="btn btn-outline-info btn-lg mb-3" data-toggle="modal" data-target="#historicoModal">
                <i class="fas fa-history mr-2"></i>
                Histórico
            </button>
            
            @php
                $usuarioLogado = auth()->user();
                $ehCriadorDoPpp = $ppp->user_id === $usuarioLogado->id;
                $ehGestorAtual = $ppp->gestor_atual_id === $usuarioLogado->id;
                $temPerfilDAF = $usuarioLogado->hasRole('daf');
                
                // Nova lógica: mostrar botões se:
                // 1. É o gestor atual (independente de ser criador ou não)
                // 2. OU tem perfil DAF (mesmo se for criador)
                // 3. E NÃO é apenas o criador sem ser gestor ou DAF
                $podeVerBotoes = ($ehGestorAtual || $temPerfilDAF) && !($ehCriadorDoPpp && !$ehGestorAtual && !$temPerfilDAF);
            @endphp
            
            @if($podeVerBotoes)
                <button type="button" class="btn btn-outline-success btn-lg mb-3" data-toggle="modal" data-target="#aprovarModal">
                    <i class="fas fa-check mr-2"></i>
                    Aprovar
                </button>
                <button type="button" class="btn btn-outline-warning btn-lg mb-3" data-toggle="modal" data-target="#solicitarCorrecaoModal">
                    <i class="fas fa-edit mr-2"></i>
                    Solicitar Correção
                </button>
                <button type="button" class="btn btn-outline-danger btn-lg mb-3" data-toggle="modal" data-target="#reprovarModal">
                    <i class="fas fa-times mr-2"></i>
                    Reprovar
                </button>
            @endif
            
            <a href="{{ route('ppp.edit', $ppp->id) }}" class="btn btn-outline-primary btn-lg mb-3">
                <i class="fas fa-edit mr-2"></i>
                Editar
            </a>
            
            <a href="{{ route('ppp.index') }}" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left mr-2"></i>
                Retornar
            </a>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

<!-- Modal de Histórico -->
<div class="modal fade" id="historicoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
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
                                            Status alterado de <strong>{{ $historico->statusAnterior->nome ?? 'Status anterior' }}</strong> 
                                            para <strong>{{ $historico->statusAtual->nome ?? 'Status atual' }}</strong>
                                        @else
                                            PPP foi criado com status <strong>{{ $historico->statusAtual->nome ?? 'Rascunho' }}</strong>
                                        @endif
                                    </p>
                                    @if($historico->justificativa)
                                        <div class="alert alert-light p-2 mt-2">
                                            <strong>💬 Mensagem:</strong> {{ $historico->justificativa }}
                                        </div>
                                    @endif
                                    <small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $historico->created_at ? $historico->created_at->format('d/m/Y H:i') : 'Data não disponível' }}
                                        @if($historico->usuario)
                                            <br><i class="fas fa-user mr-1"></i>por {{ $historico->usuario->name }}
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
        </div>
    </div>
</div>

<!-- Modal de Aprovação -->
<div class="modal fade" id="aprovarModal" tabindex="-1" role="dialog" aria-labelledby="aprovarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="aprovarModalLabel">
                    <i class="fas fa-check-circle mr-2"></i>Aprovar PPP
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('ppp.aprovar', $ppp->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Você está prestes a aprovar o PPP <strong>{{ $ppp->nome_item }}</strong>.
                        O PPP será encaminhado para o próximo nível da hierarquia.
                    </div>
                    
                    <div class="form-group">
                        <label for="comentario">Comentário (opcional)</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="3" 
                                placeholder="Adicione um comentário sobre a aprovação (opcional)..."></textarea>
                        <small class="form-text text-muted">
                            Este comentário será registrado no histórico do PPP.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i>Confirmar Aprovação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Reprovação -->
<div class="modal fade" id="reprovarModal" tabindex="-1" role="dialog" aria-labelledby="reprovarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="reprovarModalLabel">
                    <i class="fas fa-times-circle mr-2"></i>Reprovar PPP
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('ppp.reprovar', $ppp->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle mr-2"></i>Importante: Diferença entre Reprovar e Excluir</h6>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-success"><i class="fas fa-times-circle mr-1"></i>Reprovar PPP:</h6>
                                <ul class="mb-0">
                                    <li>PPP permanece disponível para consultas futuras</li>
                                    <li>Histórico é mantido</li>
                                    <li>Pode ser editado posteriormente</li>
                                    <li>Gestor responsável é mantido</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-danger"><i class="fas fa-trash mr-1"></i>Excluir PPP:</h6>
                                <ul class="mb-0">
                                    <li><strong>Elimina o PPP do sistema permanentemente</strong></li>
                                    <li>Não pode ser recuperado</li>
                                    <li>Remove todos os dados</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Você está prestes a reprovar o PPP <strong>{{ $ppp->nome_item }}</strong>.
                        O PPP será marcado como reprovado mas permanecerá disponível para consultas e edições futuras.
                    </div>
                    
                    <div class="form-group">
                        <label for="motivo">Motivo da reprovação <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="4" 
                                placeholder="Descreva o motivo da reprovação..." required></textarea>
                        <small class="form-text text-muted">
                            Este comentário será registrado no histórico do PPP e é obrigatório.
                        </small>
                        <div class="invalid-feedback">
                            O motivo da reprovação é obrigatório.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-1"></i>Confirmar Reprovação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Solicitar Correção -->
<div class="modal fade" id="solicitarCorrecaoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Solicitar Correção
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('ppp.solicitar-correcao', $ppp->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="motivo">Motivo da Correção *</label>
                        <textarea name="motivo" id="motivo" class="form-control" rows="4" 
                                placeholder="Descreva o que precisa ser corrigido..." required></textarea>
                        <small class="form-text text-muted">
                            Este comentário será registrado no histórico do PPP.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-edit mr-1"></i>Solicitar Correção
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@stop

@section('css')
<style>
/* ---------- CORES PERSONALIZADAS ---------- */
.bg-purple {
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%) !important;
}
.card-purple {
    border-color: #6f42c1 !important;
}
.bg-orange {
    background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%) !important;
}
.card-orange {
    border-color: #fd7e14 !important;
}

/* ---------- COMPONENTES DE EXIBIÇÃO ---------- */
.info-group {
    margin-bottom: 0.75rem;
}
.info-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
    display: block;
}
.info-value {
    font-size: 0.95rem;
    font-weight: 500;
    color: #495057;
    line-height: 1.4;
}
.info-value-text {
    font-size: 0.9rem;
    color: #6c757d;
    line-height: 1.5;
    max-height: 4.5rem;
    overflow-y: auto;
    padding: 0.5rem 0.75rem !important;
}

/* ---------- RESPONSIVIDADE ---------- */
@media (max-width: 768px) {
    .info-value {
        font-size: 0.85rem;
    }
    .info-value-text {
        font-size: 0.8rem;
        max-height: 3rem;
    }
    .badge-lg {
        font-size: 0.75rem !important;
    }
}

/* ---------- TIMELINE HISTÓRICO ---------- */
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-left: 2px solid #e9ecef;
}
.timeline-item:last-child {
    border-left: none;
}
.timeline-marker {
    position: absolute;
    left: -8px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}
.timeline-content {
    margin-left: 25px;
}
.timeline-title {
    margin-bottom: 8px;
    font-weight: 600;
    color: #495057;
    font-size: 1rem;
}
.timeline-text {
    margin-bottom: 8px;
    color: #6c757d;
    line-height: 1.5;
}
/* Cores adicionais usadas em marcadores do histórico */
.bg-orange {
    background-color: #fd7e14 !important;
}
.bg-purple {
    background-color: #6f42c1 !important;
}
</style>
@stop
