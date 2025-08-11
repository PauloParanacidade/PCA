# PCA - Sistema de Planejamento de Contratações Anual
## Regras Consolidadas do Projeto

> **IMPORTANTE**: Este arquivo consolida todas as regras do projeto. As informações mais recentes têm prioridade sobre as antigas.

---

## 🎯 REGRA FUNDAMENTAL DE PADRONIZAÇÃO

**ATENÇÃO**: É importante reforçar que toda funcionalidade que for utilizada nos arquivos filhos então deverá ser padronizado no layout. Sempre que detectar que 2 filhos utilizam uma mesma funcionalidade, então o layout deverá prever isso e os filhos precisarão ser ajustados, chamando essa funcionalidade do layout. Após o layout ser ajustado é importante verificar se o terceiro filho também não estaria utilizando aquela funcionalidade também. Caso afirmativo ele deverá sofrer o mesmo ajuste dos outros filhos. 

**Rotina de Análise Obrigatória**: Sempre que o código do layout ou de um dos filhos sofrer alguma alteração deve-se fazer essa rotina de análise: mais do que 1 filho está utilizando a mesma funcionalidade? Se sim, implementar no layout e ajustar para que os filhos chamem a funcionalidade do layout. Sempre que um novo filho for criado, com suas particularidades implementadas, ele utilizará o layout como base.

---

## 📋 PLANO DE REFATORAÇÃO - VIEWS PPP

### Análise Atual das Duplicações

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

### Arquitetura da Solução

#### Estrutura de Arquivos Proposta

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

#### CSS e JavaScript

```
resources/
├── css/
│   └── ppp-lista.css                 # Estilos específicos das listas
└── js/
    ├── ppp-lista-base.js             # JavaScript comum
    ├── ppp-secretaria.js             # Funcionalidades da secretária
    └── ppp-acompanhar.js             # Lógica específica do acompanhamento
```

---

## 📝 ESPECIFICAÇÃO FUNCIONAL DO SISTEMA

### 1. Criação do PPP (Pedido de Planejamento e Proposta)

#### 1.1 Estrutura Inicial
* O formulário será iniciado exibindo **apenas o card azul**
* Ao clicar em **Salvar**:
  * Um novo registro PPP é criado com status **rascunho**
  * Os demais cards (amarelo, ciano e verde) passam a ser exibidos
  * O card azul permanece **editável e preenchido**
  * Um **efeito animado e profissional** indicará visualmente a transição para os novos cards
  * **Desconsidera a funcionalidade de autosave** e remover o segundo botão redundante de salvar

#### 1.2 Campos obrigatórios
* Todos os campos dos cards azul, amarelo, ciano e verde são obrigatórios, com as **mesmas regras de validação atuais** (conforme `StorePppRequest`)

#### 1.3 Botões finais
* **Botão de Salvar e Enviar para Aprovação**: envia para o próximo nível hierárquico
* **Botão Cancelar**: redireciona para a Home

### 2. Edição do PPP

#### 2.1 Comportamento
* Ao clicar em **Editar**, todo o formulário será apresentado imediatamente
* O botão "Salvar" exclusivo do card azul **não será exibido** nesse contexto
* Botões disponíveis:
  * **Salvar** (abre modal com comentário obrigatório e reenvia o PPP)
  * **Histórico**
  * **Retornar** (volta para tabela "Meus PPPs")

### 3. Histórico
* Ao clicar em **Histórico** na tabela, será exibida uma **modal completa** com todo o histórico do PPP (status, responsáveis, comentários, datas)
* Qualquer comentário feito durante ações de aprovação, solicitação de correção, reprovação ou envio será registrado e exibido nesta modal

### 4. Visualizar PPP
* Modal com **todos os campos preenchidos**, exibidos de forma limpa e ergonômica
* Layout responsivo com botões posicionados de forma estratégica
* Se o usuário for um gestor ou membro de SUPEX, DOM, DOE ou DAF e estiver visualizando um PPP que **não criou**:
  * O status será alterado para **em avaliação**
  * Caso o usuário saia sem tomar uma ação (aprovar, solicitar correção etc.), o status **permanece em avaliação**

### 5. Botões de Ação na Visualização

#### 5.1 Todos os usuários (exceto Usuário Externo)
* **Histórico**
* **Retornar**

#### 5.2 Gestores (SUPEX, DOM, DOE, DAF, DIREX, Conselho)
* **Aprovar**:
  * Modal com comentário **opcional**
  * Grava no histórico
  * Altera status:
    * Para **aguardando_aprovacao**, exceto se for DAF
    * Se for DAF, status vai para **aguardando_direx** (ALTERADO)
* **Solicitar correção**:
  * Modal com comentário **obrigatório**
  * Comentário vai para histórico
  * Status alterado para **aguardando_correcao**
* **Editar**: abre formulário em modo edição (comportamento descrito em 2.1)
* **Reprovar**:
  * Modal com comentário **obrigatório**
  * PPP fica **bloqueado para edições futuras**
  * Altera status para **cancelado**

### 6. Remoção do PPP
* Executa **soft delete** (`deleted_at` preenchido)
* Modal de comentário obrigatório
* Botão: **Salvar mensagem e excluir definitivamente**
* Alerta com aviso: reprovado ≠ excluído -> você tem certeza?

### 7. Tabela Meus PPPs
* Remover card azul e todos os seus componentes
* Manter apenas o card ciano, com a listagem de PPPs
* Cada coluna da tabela permite **ordenamento alfabético**
* Adicionar filtros para que gestores possam visualizar apenas PPPs **pendentes de sua validação**

### 8. **NOVO FLUXO DIREX E CONSELHO**

#### 8.1 Fluxo após aprovação DAF
* DAF aprova → status: **aguardando_direx** (ID: 8)
* Secretária consegue ver na sua tabela todos os PPPs com status **aguardando_direx**

#### 8.2 Interface da Secretária - Botões Principais
* Ao entrar em "Meus PPPs", **2 botões centralizados** entre o título "Meus PPPs" e o botão "+Novo PPP":
  * **Botão DIREX**: inicia reunião da DIREX
  * **Botão Conselho**: inicialmente **desabilitado**, habilita após geração de Excel/PDF

#### 8.3 Botão Histórico da Secretária
* **Botão Histórico** sempre visível ao lado do botão "+Novo PPP"
* Registra:
  * Início da reunião da DIREX
  * Final da reunião da DIREX
  * Excel e PDF gerados
  * Aprovação/reprovação do Conselho

#### 8.4 Reunião DIREX - Início
* Ao clicar no **botão DIREX**:
  * Modal de confirmação: "Já ordenou as PPPs no modo desejado? (por prioridade, Valor Estimado, ...) Se prosseguir, a reunião da DIREX irá seguir a sequência atual, como está. Se desejar reordenar clique em voltar. Esse ordenamento não poderá ser mais alterado após o início da reunião na DIREX."
  * Se **Prosseguir**: inicia reunião com o primeiro PPP do ordenamento
  * Se **Voltar**: retorna à tabela para reordenação

#### 8.5 Durante a Reunião DIREX
* **Visualização do PPP**: status alterado para **direx_avaliando** (ID: 9) + histórico
* **Ações disponíveis**:
  * **Editar**: se salvar → status **direx_editado** (ID: 10) + histórico
  * **Incluir na tabela PCA**: status → **aguardando_conselho** (ID: 11) + histórico + incrementar tabela Excel
  * **Reprovar**: status → **cancelado** (fluxo padrão)
* **Navegação**:
  * **Botão Próximo**: vai para próximo PPP da sequência
  * **Botão Anterior**: volta para PPP anterior
  * **Botão "Sair da reunião"**: pausa reunião, retorna à tabela (tabela fica **desabilitada**)

#### 8.6 Tabela Durante Reunião DIREX
* Tabela fica **completamente desabilitada** (não permite cliques individuais)
* Permite apenas **scroll e paginação** para visualização
* Para retomar reunião: clicar novamente no **botão DIREX** (sem modal de confirmação)

#### 8.7 Final da Reunião DIREX
* Quando todos os PPPs forem avaliados:
  * **Botão "Reunião DIREX encerrada"**
  * Retorna à tabela (ainda desabilitada)
  * **Botão DIREX** é substituído por:
    * **Botão "Gerar Excel"**
    * **Botão "Gerar PDF"**
  * **Botão Conselho** permanece desabilitado

#### 8.8 Geração de Relatórios
* Após clicar em **"Gerar Excel"** ou **"Gerar PDF"**:
  * **Botão Conselho** fica **habilitado**
  * Botões de geração ficam **desabilitados**

#### 8.9 Aprovação do Conselho
* Ao clicar no **botão Conselho**:
  * Modal: "Conselho aprovou o PCA do Paranacidade?"
  * Opções: **Sim**, **Não**, **Voltar**
* **Se Sim**: todos os PPPs da tabela final → status **conselho_aprovado** (ID: 12)
* **Se Não**: todos os PPPs da tabela final → status **conselho_reprovado** (ID: 13)
* Após escolha: **botão Conselho** fica novamente **desabilitado**
* **Fim do fluxo MVP**

### 9. Tabela PCA (Planejamento de Contratações Anual)
* Contém todos os PPPs com status **aguardando_conselho** ou superior
* Cada entrada recebe **ID incremental** após inclusão pela secretária
* Campos:
  * Todos os campos do `form.blade.php`
  * `id` como primeira coluna
* Layout:
  * Funciona como uma planilha Excel com:
    * Títulos congelados no topo
    * Colunas "id" e "nome_item" fixadas na esquerda
  * Mostra **total da coluna Valor total estimado (exercício)**
  * Mostra também o que foi de cada "Origem do recurso": Paranacidade, FDU e BID/FDU

### 10. Campo "Valor se +1 exercício" (Card Verde)
* Novo comportamento:
  * Campo **"Valor se +1 exercício" permanece**
  * Novo campo ao lado: **"Anos restantes de vigência"**
  * Ao preencher os dois:
    * Gerar campos dinamicamente para cada ano restante
    * Cada campo:
      * Mostra o ano correspondente (ex: 2026, 2027...)
      * Vem **pré-preenchido** com o valor definido para "Valor se +1 exercício"
      * O primeiro ano **não é editável** — valor vem do campo "Valor total estimado (exercício)"
      * O último ano recebe o **valor restante**, calculado automaticamente
    * Caso falte saldo:
      * Exibir alerta
      * Destaque visual (ex: borda ou sombreamento vermelho)
  * Importante: a **justificativa do valor estimado** refere-se apenas ao **valor do próximo exercício** (2026)

### 11. Permissões e Hierarquia

#### 11.1 Perfis e Acesso
* **Admin**: acesso total
* **DAF**:
  * Acesso aos PPPs próprios
  * Acesso aos PPPs dos setores SUPEX, DOM, DOE
* **Gestores e SUPEX, DOM, DOE**:
  * Acesso aos próprios PPPs
  * Acesso aos PPPs enviados por subordinados (com base no campo `manager` do usuário)
* **Usuário comum**:
  * Acesso apenas aos próprios PPPs
* **Usuário externo**:
  * Sem acesso por enquanto
* **Secretária (Vera Morais Ferreira)**:
  * Perfil definido via migration/seeder
  * **NOVO**: Acesso completo ao fluxo DIREX e Conselho
  * Pode visualizar todos os PPPs na tabela PCA
  * **NOVOS** Botões disponíveis:
    * **DIREX** (inicia reunião)
    * **Conselho** (aprovação final)
    * **Gerar PDF**
    * **Gerar Planilha Excel**
    * **Histórico** (específico da secretária)
  * Também pode **criar PPPs normalmente**, como qualquer funcionário

#### 11.2 Campo `manager`
* Utilizado para **identificar o gestor imediato** e seu setor
* Extraído no momento do login
* Usado para **definir o próximo avaliador** do PPP
* Exceção:
  * Quando o avaliador for SUPEX, DOM ou DOE → encaminha para DAF, ignorando `manager`

### 12. Estrutura Técnica e Arquitetura
* **Rotas RESTful**
* **Controller único:** `PppController.php`, contendo os métodos CRUD e demais ações (aprovação, solicitação de correção, etc.)
* **Validação:**
  * `StorePppRequest.php` será usada para create e update
  * Mensagens de validação mantidas
* **Services:**
  * Centralização da lógica de negócio
  * Atualize serviços existentes conforme necessário
  * Crie novos serviços se a lógica justificar
* **Migrations:**
  * Atualizadas para refletir os campos finais
  * Evitar uso de migrations adicionais para adicionar/remover campos
* **Views:**
  * Views complexas usarão partials (ex: `form.blade.php`)
  * **Modals bem estruturadas**, claras e reutilizáveis

---

## 📊 STATUS DO SISTEMA PPP - ATUALIZADO

Baseado no arquivo `PPPStatusSeeder.php`, o sistema possui os seguintes status:

### Status Principais
| ID | Nome | Slug | Descrição | Cor |
|----|------|------|-----------|-----|
| 1 | Rascunho | `rascunho` | PPP em elaboração pelo usuário | Cinza (#6c757d) |
| 2 | Aguardando Aprovação | `aguardando_aprovacao` | PPP enviado para aprovação | Azul claro (#17a2b8) |
| 3 | Em Avaliação | `em_avaliacao` | PPP sendo avaliado pelo gestor | Amarelo (#ffc107) |
| 4 | Aguardando Correção | `aguardando_correcao` | PPP retornado para correção | Laranja (#fd7e14) |
| 5 | Em Correção | `em_correcao` | PPP sendo corrigido pelo usuário | Roxo (#6f42c1) |
| 6 | Cancelado | `cancelado` | PPP cancelado | Vermelho (#dc3545) |

### Status DIREX e Conselho - NOVOS
| ID | Nome | Slug | Descrição | Cor |
|----|------|------|-----------|-----|
| 7 | Aguardando DIREX | `aguardando_direx` | PPP aguardando avaliação da DIREX | Verde-azulado (#20c997) |
| 8 | DIREX Avaliando | `direx_avaliando` | PPP sendo avaliado na reunião da DIREX | Azul primário (#007bff) |
| 9 | DIREX Editado | `direx_editado` | PPP editado durante reunião da DIREX | Azul claro (#17a2b8) |
| 10 | Aguardando Conselho | `aguardando_conselho` | PPP aguardando aprovação do Conselho | Índigo (#6610f2) |
| 11 | Conselho Aprovado | `conselho_aprovado` | PPP aprovado pelo Conselho | Roxo (#6f42c1) |
| 12 | Conselho Reprovado | `conselho_reprovado` | PPP reprovado pelo Conselho | Rosa (#e83e8c) |

### Regras de Transição - ATUALIZADAS

#### Aprovação e Reprovação
- **Métodos `aprovar()` e `reprovar()`** aceitam PPPs com status:
  - Status 2 (`aguardando_aprovacao`) - PPP ainda não visualizado pelo gestor
  - Status 3 (`em_avaliacao`) - PPP já visualizado pelo gestor

#### Visualização por Gestores
- Quando um gestor **visualiza** um PPP com status `aguardando_aprovacao` (2), o status é automaticamente alterado para `em_avaliacao` (3)
- Visualizações subsequentes mantêm o status `em_avaliacao` (3)

#### Fluxo DAF → DIREX
- Quando DAF aprova um PPP, o status muda para `aguardando_direx` (8)
- Secretária visualiza PPPs com este status para iniciar reunião DIREX

#### Fluxo DIREX
- Visualização durante reunião: status → `direx_avaliando` (9)
- Edição durante reunião: status → `direx_editado` (10)
- Inclusão na tabela PCA: status → `aguardando_conselho` (11)

#### Fluxo Conselho
- Aprovação do Conselho: status → `conselho_aprovado` (12)
- Reprovação do Conselho: status → `conselho_reprovado` (13)

---

## 📝 NOVA FUNCIONALIDADE: "PPPs para Acompanhar"

### Regras de Negócio

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

### Interface
- **Menu**: Abaixo de "Meus PPPs"
- **Nome**: "PPPs para Acompanhar"
- **Layout**: Herda do layout base
- **Filtros**: Por subordinado, status, período
- **Colunas adicionais**: Responsável atual, Último status

---

## 🔧 IMPLEMENTAÇÃO DETALHADA

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

---

## 📅 CRONOGRAMA DE IMPLEMENTAÇÃO

### **Até 20 de Janeiro de 2025**
- Completar FASE 1 (Adequação às Especificações Core)
- Formulário funcionando conforme especificação
- Sistema de status automático implementado

### **Até 5 de Fevereiro de 2025**
- Finalizar FASE 2 (Funcionalidades Específicas)
- Todos os modos de edição e visualização
- Sistema de histórico completo

### **Até 20 de Fevereiro de 2025**
- Completar FASE 3 (Funcionalidades Avançadas)
- Tabela PCA funcional
- Sistema de hierarquia implementado

### **Até 28 de Fevereiro de 2025**
- Finalizar FASE 4 (Preparação para Produção)
- Testes extensivos
- Deploy em ambiente de homologação

### **Março de 2025**
- Homologação com usuários reais
- Ajustes finais baseados no feedback
- Preparação para implementação das notificações

---

## 🚨 IMPLEMENTAÇÃO PRIORITÁRIA

1. ✅ Atualizar PPPStatusSeeder com novos status
2. ✅ Corrigir método incluirNaPca() para aceitar status aguardando_direx
3. 🔄 Implementar interface da secretária com botões DIREX/Conselho
4. 🔄 Implementar lógica de reunião DIREX
5. 🔄 Implementar navegação Próximo/Anterior durante reunião
6. 🔄 Implementar geração de Excel/PDF
7. 🔄 Implementar aprovação do Conselho
8. 🔄 Implementar histórico específico da secretária

---

## 📋 CHECKLIST DE IMPLEMENTAÇÃO

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

---

## 🎯 BENEFÍCIOS ESPERADOS

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

---

## 📊 ESTIMATIVA DE TEMPO

- **FASE 1**: 2-3 dias
- **FASE 2**: 2-3 dias  
- **FASE 3**: 3-4 dias
- **FASE 4**: 1-2 dias

**Total**: 8-12 dias úteis

---

## 🔍 CONSIDERAÇÕES DE SEGURANÇA

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

---

## 📝 OBSERVAÇÕES IMPORTANTES

1. **Aderência Total**: Este plano segue rigorosamente a especificação consolidada
2. **Priorização**: Funcionalidades core da especificação têm prioridade sobre melhorias gerais
3. **Validação Contínua**: Cada funcionalidade deve ser validada contra a especificação
4. **Documentação**: Manter este arquivo atualizado com implementações
5. **Flexibilidade**: Notificações ficam para segunda fase conforme especificado

---

**Data de Consolidação**: Janeiro de 2025  
**Versão**: 1.0 (Consolidada)  
**Responsável**: Equipe de Desenvolvimento PCA  
**Próxima Revisão**: 20 de Janeiro de 2025  
**Arquivos Originais**: 
- plano-refatoracao-views.md.old (mais recente - prioridade)
- project_rules.md.old
- plano-desenvolvimento-refatorado.md.old

Este documento garante uma refatoração segura e eficiente, eliminando duplicação de código e preparando o sistema para futuras expansões, seguindo rigorosamente a regra de padronização de funcionalidades no layout base.