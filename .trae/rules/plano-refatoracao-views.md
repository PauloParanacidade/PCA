# Plano de Refatoração - Views PPP e Nova Funcionalidade "PPPs para Acompanhar"

## 📋 Análise Atual

### Estruturas Duplicadas Identificadas

Após análise detalhada dos arquivos `ppp/index.blade.php` (999 linhas) e `ppp/meus.blade.php` (503 linhas), foram identificadas as seguintes duplicações:

#### 1. Estrutura HTML Comum
- **Card principal** com header gradiente
- **Tabela responsiva** com classes Bootstrap idênticas
- **Sistema de paginação** Laravel
- **Modais de histórico** e exclusão
- **Alertas de feedback** do sistema

#### 2. CSS Duplicado
- **Estilos de card** (border-radius, overflow, padding)
- **Gradientes** para headers (bg-gradient-primary, bg-gradient-info)
- **Estilos de tabela** (hover effects, responsive)
- **Timeline do histórico** (markers, content, cores)
- **Animações** de hover e transições

#### 3. JavaScript Duplicado
- **Funções de exclusão** (confirmarExclusao, validarComentarioEProsseguir)
- **Inicialização jQuery** e event handlers
- **Clique em linhas** da tabela para navegação
- **Auto-hide de alertas**
- **Controle de modais**

### Diferenças Principais

#### `index.blade.php` (PPPs para Avaliar)
- **Filtros avançados** (status, busca)
- **Funcionalidades da secretária** (DIREX, Conselho, relatórios)
- **Navegação especial** durante reuniões
- **999 linhas** de código

#### `meus.blade.php` (Meus PPPs)
- **Botão "Novo PPP"**
- **Foco em PPPs próprios** do usuário
- **Interface mais simples**
- **503 linhas** de código

## 🎯 Objetivos da Refatoração

### 1. Eliminar Duplicação de Código
- Criar layout base reutilizável
- Extrair componentes comuns
- Centralizar estilos e scripts

### 2. Facilitar Manutenção
- Mudanças em um local refletem em todas as views
- Código mais limpo e organizado
- Melhor testabilidade

### 3. Preparar para Nova Funcionalidade
- "PPPs para Acompanhar" usando a mesma base
- Estrutura extensível para futuras views

## 🏗️ Arquitetura da Solução

### Estrutura de Arquivos Proposta

```
resources/views/ppp/
├── layouts/
│   └── lista-base.blade.php          # Layout base comum
├── partials/
│   ├── filtros.blade.php             # Filtros reutilizáveis
│   ├── tabela-ppps.blade.php         # Estrutura da tabela
│   ├── modals/
│   │   ├── historico.blade.php       # Modal de histórico
│   │   ├── exclusao.blade.php        # Modals de exclusão
│   │   └── secretaria.blade.php      # Modals da secretária
│   └── botoes/
│       ├── acoes-secretaria.blade.php # Botões DIREX/Conselho
│       └── novo-ppp.blade.php        # Botão Novo PPP
├── index.blade.php                   # PPPs para Avaliar (refatorado)
├── meus.blade.php                    # Meus PPPs (refatorado)
└── acompanhar.blade.php              # PPPs para Acompanhar (novo)
```

### CSS e JavaScript

```
resources/
├── css/
│   └── ppp-lista.css                 # Estilos específicos das listas
└── js/
    ├── ppp-lista-base.js             # JavaScript comum
    ├── ppp-secretaria.js             # Funcionalidades da secretária
    └── ppp-acompanhar.js             # Lógica específica do acompanhamento
```

## 📝 Especificação da Nova Funcionalidade

### "PPPs para Acompanhar"

#### Regras de Negócio

1. **Árvore Hierárquica**
   - **Descendente**: PPPs de subordinados diretos e indiretos
   - **Ascendente**: PPPs que passaram pelo usuário logado
   - **Exceção**: SUPEX e DAF veem todos os PPPs da empresa

2. **Detecção da Árvore**
   - Campo `manager` identifica gestor imediato
   - Campo `department` identifica coordenador do setor
   - Busca recursiva até 2 níveis hierárquicos

3. **Status Relevantes**
   - PPPs que já passaram pelo usuário (histórico)
   - PPPs atualmente com subordinados
   - PPPs em qualquer status (exceto rascunho)

#### Interface

- **Menu**: Abaixo de "Meus PPPs"
- **Nome**: "PPPs para Acompanhar"
- **Layout**: Herda do layout base
- **Filtros**: Por subordinado, status, período
- **Colunas adicionais**: Responsável atual, Último status

## 🔧 Implementação Detalhada

### FASE 1: Criação do Layout Base

#### 1.1 Layout Base (`layouts/lista-base.blade.php`)

```php
@extends('adminlte::page')

@section('title', $pageTitle ?? 'PPPs')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $pageTitle ?? 'PPPs' }}</h1>
        @yield('header-actions')
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('ppp.partials.alertas')
        
        @yield('filtros')
        
        <div class="card">
            <div class="card-header {{ $cardHeaderClass ?? 'bg-gradient-primary' }}">
                <h3 class="card-title text-white">
                    <i class="{{ $cardIcon ?? 'fas fa-list' }} mr-2"></i>
                    {{ $cardTitle ?? 'Lista de PPPs' }}
                </h3>
                @yield('card-actions')
            </div>
            
            <div class="card-body p-0">
                @yield('tabela-content')
            </div>
            
            @if(isset($ppps) && $ppps->hasPages())
                <div class="card-footer">
                    {{ $ppps->links() }}
                </div>
            @endif
        </div>
    </div>
    
    @yield('modals')
@stop

@section('css')
    @vite('resources/css/ppp-lista.css')
    @yield('extra-css')
@stop

@section('js')
    @vite('resources/js/ppp-lista-base.js')
    @yield('extra-js')
@stop
```

#### 1.2 Partial da Tabela (`partials/tabela-ppps.blade.php`)

```php
<div class="table-responsive" id="tabelaPpps">
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                @yield('table-headers')
            </tr>
        </thead>
        <tbody>
            @forelse($ppps as $ppp)
                <tr class="ppp-row" data-ppp-id="{{ $ppp->id }}">
                    @yield('table-row', ['ppp' => $ppp])
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $tableColspan ?? 8 }}" class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">{{ $emptyMessage ?? 'Nenhum PPP encontrado.' }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
```

### FASE 2: Refatoração das Views Existentes

#### 2.1 `index.blade.php` Refatorado

```php
@extends('ppp.layouts.lista-base', [
    'pageTitle' => 'PPPs para Avaliar',
    'cardTitle' => 'PPPs Pendentes de Avaliação',
    'cardIcon' => 'fas fa-clipboard-check',
    'cardHeaderClass' => 'bg-gradient-primary'
])

@section('header-actions')
    @if(Auth::user()->hasRole('secretaria'))
        @include('ppp.partials.botoes.acoes-secretaria')
    @endif
@endsection

@section('filtros')
    @include('ppp.partials.filtros', ['showAdvanced' => true])
@endsection

@section('table-headers')
    <th>Nome do Item</th>
    <th>Prioridade</th>
    <th>Área Solicitante</th>
    <th>Responsável Anterior</th>
    <th>Status</th>
    <th>Valor Estimado</th>
    <th>Ações</th>
@endsection

@section('table-row')
    @include('ppp.partials.linha-ppp-avaliacao', ['ppp' => $ppp])
@endsection

@section('modals')
    @include('ppp.partials.modals.historico')
    @include('ppp.partials.modals.exclusao')
    @if(Auth::user()->hasRole('secretaria'))
        @include('ppp.partials.modals.secretaria')
    @endif
@endsection

@section('extra-js')
    @if(Auth::user()->hasRole('secretaria'))
        @vite('resources/js/ppp-secretaria.js')
    @endif
@endsection
```

#### 2.2 `meus.blade.php` Refatorado

```php
@extends('ppp.layouts.lista-base', [
    'pageTitle' => 'Meus PPPs',
    'cardTitle' => 'Meus Pedidos de Planejamento e Proposta',
    'cardIcon' => 'fas fa-user-edit',
    'cardHeaderClass' => 'bg-gradient-info'
])

@section('header-actions')
    @include('ppp.partials.botoes.novo-ppp')
@endsection

@section('table-headers')
    <th>Nome do Item</th>
    <th>Prioridade</th>
    <th>Área Solicitante</th>
    <th>Status</th>
    <th>Avaliador</th>
    <th>Valor Estimado</th>
    <th>Ações</th>
@endsection

@section('table-row')
    @include('ppp.partials.linha-ppp-meus', ['ppp' => $ppp])
@endsection

@section('modals')
    @include('ppp.partials.modals.historico')
    @include('ppp.partials.modals.exclusao')
@endsection
```

### FASE 3: Nova Funcionalidade "PPPs para Acompanhar"

#### 3.1 Controller Method

```php
// Em PppController.php
public function acompanhar(Request $request)
{
    $user = Auth::user();
    
    // Verificar se é SUPEX ou DAF (veem todos)
    if ($user->hasRole(['supex', 'daf'])) {
        $query = PcaPpp::query();
    } else {
        // Obter árvore hierárquica
        $subordinados = $this->hierarquiaService->obterSubordinados($user);
        $ppsPassaram = $this->obterPppsQuePassaramPeloUsuario($user);
        
        $query = PcaPpp::where(function($q) use ($subordinados, $ppsPassaram) {
            $q->whereIn('user_id', $subordinados)
              ->orWhereIn('id', $ppsPassaram);
        });
    }
    
    // Excluir rascunhos e PPPs próprios
    $query->where('status_id', '!=', 1)
          ->where('user_id', '!=', $user->id);
    
    // Aplicar filtros
    if ($request->filled('subordinado_id')) {
        $query->where('user_id', $request->subordinado_id);
    }
    
    if ($request->filled('status_id')) {
        $query->where('status_id', $request->status_id);
    }
    
    $ppps = $query->with(['user', 'status', 'gestorAtual'])
                  ->orderBy('updated_at', 'desc')
                  ->paginate(10)
                  ->withQueryString();
    
    // Obter lista de subordinados para filtro
    $subordinadosList = User::whereIn('id', $subordinados ?? [])
                           ->select('id', 'name')
                           ->get();
    
    return view('ppp.acompanhar', compact('ppps', 'subordinadosList'));
}
```

#### 3.2 Service Methods

```php
// Em HierarquiaService.php
public function obterSubordinados(User $gestor, $niveis = 2): array
{
    $subordinados = [];
    $this->buscarSubordinadosRecursivo($gestor, $subordinados, $niveis);
    return array_unique($subordinados);
}

private function buscarSubordinadosRecursivo(User $gestor, &$subordinados, $niveisRestantes)
{
    if ($niveisRestantes <= 0) return;
    
    // Subordinados diretos (manager = gestor)
    $diretos = User::where('manager', $gestor->id)->pluck('id')->toArray();
    $subordinados = array_merge($subordinados, $diretos);
    
    // Subordinados do mesmo department
    if ($gestor->department) {
        $mesmoDept = User::where('department', $gestor->department)
                        ->where('id', '!=', $gestor->id)
                        ->pluck('id')
                        ->toArray();
        $subordinados = array_merge($subordinados, $mesmoDept);
    }
    
    // Buscar próximo nível
    foreach ($diretos as $subordinadoId) {
        $subordinado = User::find($subordinadoId);
        if ($subordinado) {
            $this->buscarSubordinadosRecursivo($subordinado, $subordinados, $niveisRestantes - 1);
        }
    }
}
```

#### 3.3 View `acompanhar.blade.php`

```php
@extends('ppp.layouts.lista-base', [
    'pageTitle' => 'PPPs para Acompanhar',
    'cardTitle' => 'PPPs da Minha Árvore Hierárquica',
    'cardIcon' => 'fas fa-sitemap',
    'cardHeaderClass' => 'bg-gradient-success'
])

@section('filtros')
    @include('ppp.partials.filtros-acompanhar')
@endsection

@section('table-headers')
    <th>Nome do Item</th>
    <th>Solicitante</th>
    <th>Responsável Atual</th>
    <th>Status</th>
    <th>Última Atualização</th>
    <th>Valor Estimado</th>
    <th>Ações</th>
@endsection

@section('table-row')
    @include('ppp.partials.linha-ppp-acompanhar', ['ppp' => $ppp])
@endsection

@section('modals')
    @include('ppp.partials.modals.historico')
@endsection

@section('extra-js')
    @vite('resources/js/ppp-acompanhar.js')
@endsection
```

## 🚨 Prevenção de Conflitos

### 1. Compatibilidade com Código Existente
- **Manter rotas atuais** inalteradas
- **Preservar IDs e classes** CSS existentes
- **Manter JavaScript** de funcionalidades específicas

### 2. Testes de Regressão
- **Testar todas as funcionalidades** após refatoração
- **Verificar modals** e interações JavaScript
- **Validar permissões** e filtros

### 3. Rollback Plan
- **Backup dos arquivos** originais
- **Commits granulares** para facilitar reversão
- **Testes em ambiente** de desenvolvimento

## 📋 Checklist de Implementação

### ✅ FASE 1: Layout Base
- [ ] Criar `layouts/lista-base.blade.php`
- [ ] Criar partials comuns
- [ ] Extrair CSS comum para `ppp-lista.css`
- [ ] Extrair JavaScript comum para `ppp-lista-base.js`

### ✅ FASE 2: Refatoração
- [ ] Refatorar `index.blade.php`
- [ ] Refatorar `meus.blade.php`
- [ ] Testar funcionalidades existentes
- [ ] Validar responsividade

### ✅ FASE 3: Nova Funcionalidade
- [ ] Implementar método `acompanhar()` no controller
- [ ] Criar service methods para hierarquia
- [ ] Criar view `acompanhar.blade.php`
- [ ] Adicionar item no menu
- [ ] Implementar filtros específicos

### ✅ FASE 4: Testes e Ajustes
- [ ] Testes de funcionalidade
- [ ] Testes de permissões
- [ ] Validação de performance
- [ ] Ajustes de UX

## 🎯 Benefícios Esperados

### 1. Redução de Código
- **~60% menos linhas** duplicadas
- **Manutenção centralizada**
- **Consistência visual**

### 2. Melhor UX
- **Interface padronizada**
- **Navegação intuitiva**
- **Performance otimizada**

### 3. Facilidade de Desenvolvimento
- **Componentes reutilizáveis**
- **Estrutura extensível**
- **Código mais limpo**

## 📊 Estimativa de Tempo

- **FASE 1**: 2-3 dias
- **FASE 2**: 2-3 dias  
- **FASE 3**: 3-4 dias
- **FASE 4**: 1-2 dias

**Total**: 8-12 dias úteis

## 🔍 Considerações de Segurança

### 1. Permissões
- **Validar acesso** à nova funcionalidade
- **Filtrar dados** por hierarquia
- **Proteger informações** sensíveis

### 2. Performance
- **Otimizar queries** hierárquicas
- **Implementar cache** quando necessário
- **Paginar resultados** adequadamente

### 3. Auditoria
- **Registrar acessos** à nova funcionalidade
- **Manter logs** de consultas hierárquicas
- **Monitorar performance**

Este plano garante uma refatoração segura e eficiente, eliminando duplicação de código e preparando o sistema para futuras expansões.