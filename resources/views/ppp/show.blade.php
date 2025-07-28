@extends('layouts.adminlte-custom')

@section('title', 'Visualizar PPP')

@section('content_header')
@if (session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

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
                            <small class="text-white-100 d-block mt-1">
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
                            <div class="col-md-8">
                                <div class="info-group">
                                    <label class="info-label">Nome do Item</label>
                                    <div class="info-value font-weight-bold">{{ $ppp->nome_item }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-md-8">
                                <div class="info-group">
                                    <label class="info-label">Quantidade</label>
                                    <div class="info-value">{{ $ppp->quantidade ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
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
                        <label class="info-label">Natureza do Objeto</label>
                        <div class="info-value-text">
                            {{ $ppp->natureza_objeto ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="info-group">
                        <label class="info-label">Descrição/Especificação</label>
                        <div class="info-value-text">
                            {{ $ppp->descricao ?? 'N/A' }}
                        </div>
                    </div>


                    
                    <div class="info-group">
                        <label class="info-label">Justificativa da Necessidade</label>
                        <div class="info-value-text">
                            {{ $ppp->justificativa_pedido ?? 'N/A' }}
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
                        <i class="fas fa-file text-muted" style="font-size: 2.5rem; position: relative;">
                            <i class="fas fa-ban text-danger" style="position: absolute; top: -0.2rem; right: -0.5rem; font-size: 1.2rem;"></i>
                        </i>
                        <p class="text-muted mt-2 mb-0">Sem contrato vigente</p>
                    </div>
                    @if($ppp->mes_inicio_prestacao)
                        <div class="row mb-2 mt-3">
                            <div class="col-md-12">
                                <div class="info-group">
                                    <label class="info-label">Mês pretendido para início deste contrato novo</label>
                                    <div class="info-value">
                                        @php
                                        $mesesNomes = [
                                        '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                                        '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                                        '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                                        '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
                                        ];
                                        @endphp
                                        {{ $mesesNomes[$ppp->mes_inicio_prestacao] ?? $ppp->mes_inicio_prestacao }} de 2026
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
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
                $ehSecretaria = $usuarioLogado->hasRole('secretaria');
                $reuniaoDirectxAtiva = session('reuniao_direx_ativa', false);
                $modoReuniaoDirectx = $ehSecretaria && $reuniaoDirectxAtiva;
                
                // Definir permissões de visualização de botões
                $podeVerBotoes = false;
                $podeEditar = false;
                
                // Lógica para determinar se pode ver botões de ação
                if ($usuarioLogado->hasRole('admin')) {
                    $podeVerBotoes = true;
                    $podeEditar = true;
                } elseif ($ehSecretaria) {
                    // Secretária pode ver botões para PPPs aguardando DIREX ou em reunião DIREX
                    $podeVerBotoes = in_array($ppp->status_id, [8, 9, 10]); // aguardando_direx, direx_avaliando, direx_editado
                    $podeEditar = $modoReuniaoDirectx && $ppp->status_id == 9; // Só pode editar se estiver avaliando na reunião
                } elseif ($usuarioLogado->hasRole(['daf', 'gestor'])) {
                    // DAF e gestores podem ver botões para PPPs aguardando aprovação ou em avaliação
                    $podeVerBotoes = in_array($ppp->status_id, [2, 3, 7, 8, 9]) && (
                    $usuarioLogado->hasRole(['daf', 'admin', 'gestor']) // É DAF ou ADMIN
                    // $ehGestor // CORRIGIDO: verificar se tem role de gestor
                    );
                    // Gestores podem editar PPPs que podem visualizar
                    $podeEditar = $podeVerBotoes;
                } else {
                    // Usuário comum só pode ver botões dos próprios PPPs
                    $podeVerBotoes = $ppp->user_id == $usuarioLogado->id && in_array($ppp->status_id, [2, 3]);
                    // Usuário pode editar seu próprio PPP
                    $podeEditar = $ppp->user_id == $usuarioLogado->id;
                }
                @endphp
                
                @if($modoReuniaoDirectx)
                <!-- Indicador de Reunião DIREX Ativa - MELHORADO -->
                <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="reunion-indicator mr-3">
                            <i class="fas fa-users fa-2x text-warning"></i>
                            <div class="pulse-ring"></div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="alert-heading mb-1">
                                <i class="fas fa-circle text-danger blink mr-1"></i>
                                Reunião DIREX em Andamento
                                <span class="badge badge-warning ml-2">ATIVA</span>
                            </h5>
                            <p class="mb-1">Modo de navegação sequencial ativo. Use os botões Anterior/Próximo para navegar entre os PPPs.</p>
                            <small class="text-muted">
                                <i class="fas fa-clock mr-1"></i>
                                Iniciada em {{ session('reuniao_direx_inicio', now())->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div class="text-right">
                            <div class="progress-info">
                                <span class="badge badge-light badge-lg mb-1">{{ $navegacao['atual'] ?? '?' }}/{{ $navegacao['total'] ?? '?' }}</span>
                                <div class="progress" style="width: 120px; height: 10px;">
                                    <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" 
                                    role="progressbar" 
                                    style="width: {{ $navegacao ? ($navegacao['atual'] / $navegacao['total']) * 100 : 0 }}%"
                                    aria-valuenow="{{ $navegacao['atual'] ?? 0 }}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="{{ $navegacao['total'] ?? 100 }}">
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">
                                {{ $navegacao ? round(($navegacao['atual'] / $navegacao['total']) * 100) : 0 }}% concluído
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            @if($podeVerBotoes)
            <button 
            type="button" 
            class="btn btn-outline-success btn-lg mb-3" 
            data-toggle="modal" 
            data-target="#aprovarModal"
            id="btnValidarEncaminhar"
            title="{{ $ehSecretaria ? 'Incluir este PPP na tabela PCA' : 'Após sua validação, o PPP será avaliado pelo seu chefe, no próximo nível hierárquico.' }}"
            >
            <i class="fas fa-check mr-2"></i>
            {{ $ehSecretaria ? 'Incluir na tabela PCA' : 'Validar e Encaminhar' }}
        </button>
        
        @if(!$ehSecretaria)
        <button type="button" class="btn btn-outline-warning btn-lg mb-3" data-toggle="modal" data-target="#solicitarCorrecaoModal">
            <i class="fas fa-edit mr-2"></i>
            Solicitar Correção
        </button>
        <button type="button" class="btn btn-outline-danger btn-lg mb-3" data-toggle="modal" data-target="#reprovarModal">
            <i class="fas fa-times mr-2"></i>
            Reprovar
        </button>
        @endif
        @endif
        
        @if($podeEditar)
        <a href="{{ route('ppp.edit', $ppp->id) }}" class="btn btn-outline-primary btn-lg mb-3">
            <i class="fas fa-edit mr-2"></i>
            Editar
        </a>
        @else
        <button type="button" class="btn btn-outline-secondary btn-lg mb-3" disabled title="Edição não permitida">
            <i class="fas fa-edit mr-2"></i>
            Editar
        </button>
        @endif
        
        @if($modoReuniaoDirectx)
        <!-- Ações da Reunião DIREX - REORGANIZADAS -->
        <div class="card card-outline card-warning shadow-sm mb-3">
            <div class="card-header bg-warning py-2">
                <h3 class="card-title text-white mb-0">
                    <i class="fas fa-gavel mr-2"></i>
                    Ações da Reunião DIREX
                </h3>
                <div class="card-tools">
                    <span class="badge badge-light">PPP #{{ $ppp->id }}</span>
                </div>
            </div>
            <div class="card-body py-3">
                <!-- Ações Principais -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-lg btn-block mb-2 action-btn" 
                        onclick="incluirNaPcaDirectx({{ $ppp->id }})">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span class="d-none d-md-inline">Incluir na </span>Tabela PCA
                    </button>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-danger btn-lg btn-block mb-2 action-btn" 
                    data-toggle="modal" data-target="#reprovarModal">
                    <i class="fas fa-times-circle mr-2"></i>
                    Reprovar PPP
                </button>
            </div>
        </div>
        
        <!-- Ações Secundárias -->
        <div class="row">
            <div class="col-md-6">
                <button type="button" class="btn btn-info btn-lg btn-block action-btn" 
                onclick="editarDuranteDirectx({{ $ppp->id }})">
                <i class="fas fa-edit mr-2"></i>
                Editar PPP
            </button>
        </div>
        <div class="col-md-6">
            <button type="button" class="btn btn-secondary btn-lg btn-block action-btn" 
            onclick="sairDaReuniaoDirectx()">
            <i class="fas fa-pause mr-2"></i>
            Pausar Reunião
        </button>
    </div>
</div>

<!-- Status do PPP Atual -->
<div class="mt-3 pt-3 border-top">
    <div class="d-flex justify-content-between align-items-center">
        <small class="text-muted">
            <i class="fas fa-info-circle mr-1"></i>
            Status: <span class="badge badge-{{ $ppp->status->cor ?? 'secondary' }}">{{ $ppp->status->nome ?? 'N/A' }}</span>
        </small>
        <small class="text-muted">
            <i class="fas fa-user mr-1"></i>
            Criado por: {{ $ppp->user->name ?? 'N/A' }}
        </small>
    </div>
</div>
</div>
</div>
@endif

<!-- Navegação DIREX - APRIMORADA -->
@if($ehSecretaria && isset($navegacao))
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header bg-primary py-2">
        <h3 class="card-title text-white mb-0">
            <i class="fas fa-arrows-alt-h mr-2"></i>
            Navegação {{ $modoReuniaoDirectx ? 'DIREX' : 'PPPs' }}
        </h3>
        <div class="card-tools">
            @if($modoReuniaoDirectx)
            <span class="badge badge-warning">Modo DIREX</span>
            @endif
        </div>
    </div>
    <div class="card-body py-3">
        <!-- Informações de Navegação -->
        <div class="navigation-info mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted">
                    <i class="fas fa-list mr-1"></i>
                    PPP {{ $navegacao['atual'] }} de {{ $navegacao['total'] }}
                </span>
                <div class="navigation-progress">
                    <div class="progress" style="width: 100px; height: 6px;">
                        <div class="progress-bar bg-primary" 
                        style="width: {{ ($navegacao['atual'] / $navegacao['total']) * 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botões de Navegação -->
        <div class="row">
            <div class="col-6">
                @if($navegacao['anterior'])
                <button type="button" class="btn btn-outline-secondary btn-lg btn-block nav-btn" 
                onclick="navegarPppDirectx('anterior', {{ $navegacao['anterior'] }})">
                <i class="fas fa-chevron-left mr-1"></i>
                <span class="d-none d-md-inline">Anterior</span>
            </button>
            @else
            <button type="button" class="btn btn-outline-secondary btn-lg btn-block" disabled>
                <i class="fas fa-chevron-left mr-1"></i>
                <span class="d-none d-md-inline">Anterior</span>
            </button>
            @endif
        </div>
        <div class="col-6">
            @if($navegacao['proximo'])
            <button type="button" class="btn btn-outline-secondary btn-lg btn-block nav-btn" 
            onclick="navegarPppDirectx('proximo', {{ $navegacao['proximo'] }})">
            <span class="d-none d-md-inline">Próximo</span>
            <i class="fas fa-chevron-right ml-1"></i>
        </button>
        @else
        @if($modoReuniaoDirectx)
        <button type="button" class="btn btn-success btn-lg btn-block" 
        onclick="encerrarReuniaoDirectx()">
        <i class="fas fa-flag-checkered mr-1"></i>
        <span class="d-none d-md-inline">Encerrar</span> Reunião
    </button>
    @else
    <button type="button" class="btn btn-outline-secondary btn-lg btn-block" disabled>
        <span class="d-none d-md-inline">Próximo</span>
        <i class="fas fa-chevron-right ml-1"></i>
    </button>
    @endif
    @endif
</div>
</div>

<!-- Atalhos de Teclado -->
@if($modoReuniaoDirectx)
<div class="mt-3 pt-3 border-top">
    <small class="text-muted d-block text-center">
        <i class="fas fa-keyboard mr-1"></i>
        Atalhos: <kbd>←</kbd> Anterior | <kbd>→</kbd> Próximo | <kbd>Esc</kbd> Pausar
    </small>
</div>
@endif
</div>
</div>
@endif

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

<!-- Modal de Aprovação -->
<div class="modal fade" id="aprovarModal" tabindex="-1" role="dialog" aria-labelledby="aprovarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="aprovarModalLabel">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ $ehSecretaria ? 'Incluir na tabela PCA' : 'Aprovar PPP' }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ $ehSecretaria ? route('ppp.incluir-pca', $ppp->id) : route('ppp.aprovar', $ppp->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        @if($ehSecretaria)
                        Você está prestes a incluir o PPP <strong>{{ $ppp->nome_item }}</strong> na tabela PCA.
                        Este PPP será marcado como aprovado final e incluído no Plano de Contratações Anual.
                        @else
                        Você está prestes a aprovar o PPP <strong>{{ $ppp->nome_item }}</strong>.
                        O PPP será encaminhado para o próximo nível da hierarquia.
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label for="comentario">Comentário (opcional)</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="3"
                        placeholder="{{ $ehSecretaria ? 'Adicione um comentário sobre a inclusão na tabela PCA (opcional)...' : 'Adicione um comentário sobre a aprovação (opcional)...' }}"></textarea>
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
                        <i class="fas fa-check mr-1"></i>
                        {{ $ehSecretaria ? 'Confirmar Inclusão na PCA' : 'Confirmar Aprovação' }}
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

<!-- Modal Historico -->
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

@stop

@section('js')
@vite('resources/js/ppp-form.js')
<script>
    <script>
        $(document).ready(function() {
            // Verificar se estamos em modo reunião DIREX
            const modoReuniaoDirectx = {{ $modoReuniaoDirectx ? 'true' : 'false' }};
            
            if (modoReuniaoDirectx) {
                console.log('🎯 Modo Reunião DIREX ativo');
                
                // Atualizar status do PPP para 'direx_avaliando'
                atualizarStatusPppDirectx({{ $ppp->id }}, 'direx_avaliando');
                
                // Adicionar efeito de piscar no indicador
                setInterval(function() {
                    $('.blink').fadeOut(500).fadeIn(500);
                }, 1000);
            }
        });
        
        // Adicionar atalhos de teclado para navegação DIREX
        @if($modoReuniaoDirectx)
        document.addEventListener('keydown', function(e) {
            // Verificar se não está em um input/textarea
            if (e.target.tagName.toLowerCase() === 'input' || 
            e.target.tagName.toLowerCase() === 'textarea') {
                return;
            }
            
            switch(e.key) {
                case 'ArrowLeft':
                e.preventDefault();
                @if($navegacao['anterior'])
                navegarPppDirectx('anterior', {{ $navegacao['anterior'] }});
                @endif
                break;
                
                case 'ArrowRight':
                e.preventDefault();
                @if($navegacao['proximo'])
                navegarPppDirectx('proximo', {{ $navegacao['proximo'] }});
                @else
                encerrarReuniaoDirectx();
                @endif
                break;
                
                case 'Escape':
                e.preventDefault();
                sairDaReuniaoDirectx();
                break;
            }
        });
        @endif
        
        /**
        * Navegar entre PPPs durante reunião DIREX
        */
        function navegarPppDirectx(direcao, pppId) {
            console.log(`🔄 Navegando ${direcao} para PPP:`, pppId);
            
            // Construir a URL baseada na direção
            let url;
            if (direcao === 'proximo') {
                url = '{{ route("ppp.direx.proximo", ":id") }}'.replace(':id', {{ $ppp->id }});
            } else if (direcao === 'anterior') {
                url = '{{ route("ppp.direx.anterior", ":id") }}'.replace(':id', {{ $ppp->id }});
            } else {
                console.error('Direção inválida:', direcao);
                return;
            }
            
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    if (response.success && response.redirect_url) {
                        window.location.href = response.redirect_url;
                    } else {
                        mostrarAlerta(response.message || 'Erro ao navegar.', 'warning');
                    }
                },
                error: function() {
                    mostrarAlerta('Erro ao navegar entre PPPs.', 'danger');
                }
            });
        }
        
        /**
        * Incluir PPP na tabela PCA durante reunião DIREX
        */
        function incluirNaPcaDirectx(pppId) {
            if (!confirm('Confirma a inclusão deste PPP na tabela PCA?')) {
                return;
            }
            
            mostrarLoading('Incluindo na PCA...');
            
            $.ajax({
                url: `{{ route('ppp.direx.incluir-pca', ':id') }}`.replace(':id', pppId),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta(response.message, 'success');
                        
                        // Navegar para próximo PPP automaticamente
                        if (response.proximo_ppp_id) {
                            setTimeout(() => {
                                navegarPppDirectx('proximo', response.proximo_ppp_id);
                            }, 1500);
                        } else {
                            // Último PPP - mostrar opção de encerrar reunião
                            setTimeout(() => {
                                if (confirm('Este era o último PPP. Deseja encerrar a reunião DIREX?')) {
                                    encerrarReuniaoDirectx();
                                }
                            }, 2000);
                        }
                    } else {
                        mostrarAlerta(response.message, 'warning');
                    }
                },
                error: function(xhr) {
                    console.error('Erro ao incluir na PCA:', xhr);
                    mostrarAlerta('Erro ao incluir PPP na tabela PCA.', 'danger');
                },
                complete: function() {
                    ocultarLoading();
                }
            });
        }
        
        /**
        * Editar PPP durante reunião DIREX
        */
        function editarDuranteDirectx(pppId) {
            // Redirecionar para edição com parâmetro especial
            window.location.href = `{{ route('ppp.edit', ':id') }}`.replace(':id', pppId) + '?modo=direx';
        }
        
        /**
        * Sair/Pausar reunião DIREX
        */
        function sairDaReuniaoDirectx() {
            if (!confirm('Deseja pausar a reunião DIREX? Você poderá retomar posteriormente.')) {
                return;
            }
            
            mostrarLoading('Pausando reunião...');
            
            $.ajax({
                url: '/ppp/direx/pausar', // URL temporária ou implementar rota
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ppp_atual_id: {{ $ppp->id }}
                },
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta('Reunião pausada com sucesso.', 'info');
                        // Redirecionar para index
                        setTimeout(() => {
                            window.location.href = '{{ route("ppp.index") }}';
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    console.error('Erro ao pausar reunião:', xhr);
                    mostrarAlerta('Erro ao pausar reunião.', 'danger');
                },
                complete: function() {
                    ocultarLoading();
                }
            });
        }
        
        /**
        * Encerrar reunião DIREX
        */
        function encerrarReuniaoDirectx() {
            if (!confirm('Confirma o encerramento da reunião DIREX? Esta ação não pode ser desfeita.')) {
                return;
            }
            
            mostrarLoading('Encerrando reunião...');
            
            $.ajax({
                url: `{{ route('ppp.direx.encerrar-reuniao') }}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        mostrarAlerta('Reunião DIREX encerrada com sucesso!', 'success');
                        // Redirecionar para index
                        setTimeout(() => {
                            window.location.href = '{{ route("ppp.index") }}';
                        }, 2000);
                    }
                },
                error: function(xhr) {
                    console.error('Erro ao encerrar reunião:', xhr);
                    mostrarAlerta('Erro ao encerrar reunião.', 'danger');
                },
                complete: function() {
                    ocultarLoading();
                }
            });
        }
        
        /**
        * Atualizar status do PPP durante DIREX
        */
        function atualizarStatusPppDirectx(pppId, novoStatus) {
            $.ajax({
                url: '/ppp/direx/atualizar-status', // URL temporária ou implementar rota
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ppp_id: pppId,
                    status: novoStatus
                },
                success: function(response) {
                    console.log('✅ Status atualizado:', response);
                },
                error: function(xhr) {
                    console.error('❌ Erro ao atualizar status:', xhr);
                }
            });
        }
        
        /**
        * Funções auxiliares
        */
        function mostrarLoading(texto = 'Carregando...') {
            // Implementar overlay de loading
            $('body').append(`
                <div id="loadingOverlay" class="loading-overlay">
                    <div class="loading-content">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                        <p class="mb-0">${texto}</p>
                    </div>
                </div>
            `);
        }
        
        function ocultarLoading() {
            $('#loadingOverlay').remove();
        }
        
        function mostrarAlerta(mensagem, tipo = 'info') {
            const alertClass = `alert-${tipo}`;
            const iconClass = {
                'success': 'fa-check-circle',
                'danger': 'fa-exclamation-triangle',
                'warning': 'fa-exclamation-circle',
                'info': 'fa-info-circle'
            }[tipo] || 'fa-info-circle';
            const alerta = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas ${iconClass} mr-2"></i>
                    ${mensagem}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `;
            $('.content-header').after(alerta);
            
            // Auto-remover após 5 segundos
            setTimeout(() => {
                $('.alert').fadeOut();
            }, 5000);
        }
    </script>
    
    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .blink {
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }
    </style>
</script>
@endsection

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
    
    /* ---------- COMPONENTES DE EXIBIÇÃO - MELHORADOS ---------- */
    .info-group {
        margin-bottom: 0.6rem; /* Reduzido de 0.75rem */
    }
    .info-label {
    font-size: 0.85rem;
    font-weight: 700; /* de 600 para 700 */
    color: #343a40;   /* de #6c757d para cor mais escura */
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 0.35rem;
    display: block;
}

    .info-value {
        font-size: 1.1rem; /* Aumentado de 0.95rem */
        font-weight: 500;
        color: #495057;
        line-height: 1.4;
    }
    .info-value-text {
        font-size: 1rem; /* Aumentado de 0.9rem */
        color: #6c757d;
        line-height: 1.5;
        max-height: 5rem; /* Aumentado de 4.5rem */
        overflow-y: auto;
        padding: 0.4rem 0.6rem !important; /* Reduzido de 0.5rem 0.75rem */
    }
    
    /* ---------- CARDS COM ALTURA FLEXÍVEL ---------- */
    .card.h-100 {
        height: auto !important; /* Permite crescimento vertical */
        min-height: 100%; /* Mantém altura mínima igual */
    }
    
    .card-body {
        padding: 0.75rem 1rem; /* Reduzido de py-3 (1rem) */
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    /* Garantir que cards fiquem alinhados horizontalmente */
    .row.mb-3 {
        display: flex;
        align-items: stretch; /* Estica cards para mesma altura base */
    }
    
    .col-lg-6 {
        display: flex;
        flex-direction: column;
    }
    
    /* ---------- BADGES MAIORES ---------- */
    .badge-lg {
        font-size: 0.9rem !important; /* Aumentado de padrão */
        padding: 0.5rem 0.75rem !important;
    }
    
    /* ---------- RESPONSIVIDADE MELHORADA ---------- */
    @media (max-width: 768px) {
        .info-value {
            font-size: 1rem; /* Aumentado de 0.85rem */
        }
        .info-value-text {
            font-size: 0.9rem; /* Aumentado de 0.8rem */
            max-height: 4rem; /* Aumentado de 3rem */
        }
        .badge-lg {
            font-size: 0.8rem !important;
        }
        .card-body {
            padding: 0.6rem 0.8rem; /* Ajustado para mobile */
        }
    }
    
    @media (max-width: 576px) {
        .col-lg-6 {
            margin-bottom: 1rem;
        }
        .info-value {
            font-size: 0.95rem;
        }
        .info-value-text {
            font-size: 0.85rem;
            max-height: 3.5rem;
        }
    }
    
    /* ---------- SIDEBAR FIXA ---------- */
    @media (min-width: 992px) {
        .col-lg-3 {
            position: sticky;
            top: 1rem;
            height: fit-content;
        }
    }
    
    /* ---------- BOTÕES PADRONIZADOS ---------- */
    .btn-historico {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        border: 1px solid #ffc107;
        color: #212529;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-historico:hover {
        background: linear-gradient(135deg, #e0a800 0%, #d39e00 100%);
        border-color: #d39e00;
        color: #212529;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
    }
    
    .btn-historico i {
        margin-right: 0.5rem;
    }
    
    /* Tamanhos específicos para diferentes contextos */
    .btn-historico.btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .btn-historico.btn-lg {
        padding: 0.5rem 1rem;
        font-size: 1.125rem;
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
    
    /* ---------- MELHORIAS ESPECÍFICAS PARA CAMPOS LONGOS ---------- */
    .info-value-text {
        word-wrap: break-word;
        word-break: break-word;
        hyphens: auto;
    }
    
    /* Garantir que textos longos não quebrem o layout */
    .info-value {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    /* ---------- AJUSTES PARA MANTER ALINHAMENTO ---------- */
    .row.mb-3 .col-lg-6:nth-child(odd) {
        padding-right: 0.75rem;
    }
    
    .row.mb-3 .col-lg-6:nth-child(even) {
        padding-left: 0.75rem;
    }
    
    @media (max-width: 991px) {
        .row.mb-3 .col-lg-6:nth-child(odd),
        .row.mb-3 .col-lg-6:nth-child(even) {
            padding-left: 15px;
            padding-right: 15px;
        }
    }
    
    /* ---------- ESTILOS PARA REUNIÃO DIREX ---------- */
    /* Indicador de reunião com animação */
    .reunion-indicator {
        position: relative;
        display: inline-block;
    }
    
    .pulse-ring {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 60px;
        height: 60px;
        border: 3px solid #ffc107;
        border-radius: 50%;
        animation: pulse-ring 2s infinite;
        opacity: 0;
    }
    
    @keyframes pulse-ring {
        0% {
            transform: translate(-50%, -50%) scale(0.8);
            opacity: 1;
        }
        100% {
            transform: translate(-50%, -50%) scale(1.5);
            opacity: 0;
        }
    }
    
    /* Botões de ação com hover melhorado */
    .action-btn {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .action-btn:active {
        transform: translateY(0);
    }
    
    /* Botões de navegação */
    .nav-btn {
        transition: all 0.2s ease;
    }
    
    .nav-btn:hover {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
    }
    
    /* Informações de progresso */
    .progress-info {
        text-align: center;
    }
    
    .navigation-info {
        background-color: #f8f9fa;
        padding: 0.75rem;
        border-radius: 0.375rem;
        border: 1px solid #e9ecef;
    }
    
    /* Responsividade para mobile */
    @media (max-width: 768px) {
        .action-btn {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }
        
        .nav-btn {
            font-size: 0.9rem;
        }
        
        .reunion-indicator .pulse-ring {
            width: 45px;
            height: 45px;
        }
    }
</style>
@endsection
