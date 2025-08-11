# Plano de Desenvolvimento PCA - Versão Refatorada

Baseado na **Especificação Funcional do Sistema PCA** definida em `project_rules.md`, este documento apresenta o plano de desenvolvimento reorganizado considerando as funcionalidades específicas e requisitos técnicos detalhados.

## 📋 Status Atual vs. Especificação

### ✅ Funcionalidades Implementadas

1. **Estrutura Base do Sistema**
   - ✅ Framework Laravel configurado
   - ✅ Autenticação via LDAP implementada
   - ✅ Sistema de roles e permissões básico
   - ✅ Middleware de segurança

2. **Módulo de Cadastro de PPP (Parcial)**
   - ✅ Formulário com 4 cards (azul, amarelo, verde, ciano)
   - ✅ Validações de campos (`StorePppRequest`)
   - ✅ Persistência no banco de dados
   - ✅ Criação do rascunho utilizando as informações apenas do card azul
   - ✅ Animação profissional na transição dos cards
   - ✅ Remoção do segundo botão redundante de salvar

3. **Correções de Interface**
   - ❌ Validação prematura, nos cards Verde e Ciano

### 🔄 Funcionalidades Parcialmente Implementadas

1. **Sistema de Status**
   - ✅ Definição dos 8 status (incluindo `aprovado_direx`)
   - ❌ **PENDENTE**: Lógica completa de transição automática
   - ❌ **PENDENTE**: Status `em_avaliacao` automático para gestores

2. **Fluxo de Aprovação**
   - ✅ Estrutura básica implementada
   - ❌ **PENDENTE**: Modais específicas para cada ação
   - ❌ **PENDENTE**: Lógica de hierarquia completa

## 🎯 Plano de Desenvolvimento Refatorado

### **FASE 1: Adequação às Especificações Core (Prioridade Crítica)**

#### 1.1 Formulário de Criação de PPP
- [ ] **Implementar exibição inicial apenas do card azul**
  - Ocultar cards amarelo, verde e ciano inicialmente
  - Exibir apenas após primeiro salvamento
  - Manter card azul editável após salvamento

- [ ] **Implementar animação profissional**
  - Criar efeito de transição para novos cards após a criação do Rascunho
  - Transição suave e profissional
  - Feedback visual claro para o usuário

- [ ] **Remover funcionalidades desnecessárias**
  - Remover autosave
  - Remover segundo botão redundante de salvar
  - Manter apenas botões finais: "Salvar e Enviar para Aprovação" e "Cancelar"

#### 1.2 Sistema de Status Automático
- [ ] **Implementar mudança automática para `em_avaliacao`**
  - Detectar quando gestor (SUPEX, DOM, DOE, DAF) visualiza PPP que não criou
  - Alterar status automaticamente
  - Manter status se usuário sair sem ação

- [ ] **Implementar lógica de transição de status**
  - `rascunho` → `aguardando_aprovacao` (ao enviar)
  - `aguardando_aprovacao` → `em_avaliacao` (ao visualizar)
  - `em_avaliacao` → `aguardando_aprovacao` (aprovação não-DAF)
  - `em_avaliacao` → `aprovado_final` (aprovação DAF)
  - `em_avaliacao` → `aguardando_correcao` (solicitar correção)
  - `em_avaliacao` → `cancelado` (reprovar)

#### 1.3 Modais de Ação Específicas
- [ ] **Modal de Aprovação**
  - Comentário opcional
  - Gravação no histórico
  - Lógica específica para DAF (aprovado_final + tabela PCA)

- [ ] **Modal de Solicitação de Correção**
  - Comentário obrigatório
  - Status para `aguardando_correcao`
  - Registro no histórico

- [ ] **Modal de Reprovação**
  - Comentário obrigatório
  - Bloqueio para edições futuras
  - Status para `cancelado`

- [ ] **Modal de Remoção**
  - Soft delete (`deleted_at`)
  - Comentário obrigatório
  - Alerta: "reprovado ≠ excluído"
  - Botão: "Salvar mensagem e excluir definitivamente"

### **FASE 2: Funcionalidades Específicas da Especificação (Prioridade Alta)**

#### 2.1 Edição de PPP
- [ ] **Implementar modo de edição específico**
  - Exibir formulário completo imediatamente
  - Ocultar botão "Salvar" do card azul
  - Botões: Salvar (com modal), Histórico, Retornar
  - Modal de salvamento com comentário obrigatório

#### 2.2 Visualização de PPP
- [ ] **Modal de visualização completa**
  - Todos os campos preenchidos
  - Layout limpo e ergonômico
  - Responsivo com botões estratégicos
  - Botões baseados no perfil do usuário

#### 2.3 Sistema de Histórico Completo
- [ ] **Modal de histórico detalhada**
  - Status, responsáveis, comentários, datas
  - Registro de todas as ações
  - Interface clara e cronológica

#### 2.4 Tabela "Meus PPPs" Reformulada
- [ ] **Remover card azul da listagem**
  - Manter apenas card ciano
  - Implementar ordenamento alfabético por coluna
  - Adicionar filtros para gestores (PPPs pendentes)

### **FASE 3: Funcionalidades Avançadas (Prioridade Alta)**

#### 3.1 Tabela PCA (Planejamento de Contratações Anual)
- [ ] **Implementar tabela PCA completa**
  - Todos os PPPs com status `aprovado_final`
  - ID incremental após aprovação DAF
  - Layout tipo planilha Excel
  - Títulos congelados no topo
  - Colunas "id" e "nome_item" fixadas
  - Totalizadores por "Origem do recurso"

#### 3.2 Sistema de Hierarquia e Permissões
- [ ] **Implementar hierarquia completa**
  - Campo `manager` para identificar gestor imediato
  - Lógica de encaminhamento por nível
  - Exceção: SUPEX/DOM/DOE → DAF
  - Permissões específicas por perfil

#### 3.5 Perfil Secretária (Vera Morais Ferreira)
- [ ] **Implementar funcionalidades específicas**
  - Visualização de todos os PPPs na tabela PCA
  - Botões: Aprovar todas, Aprovar individualmente
  - Geração de PDF e Excel
  - Status `aprovado_direx` para suas aprovações
  - Capacidade de criar PPPs normalmente

### **FASE 4: Preparação para Produção (Prioridade Alta)**

#### 4.1 Testes de Fluxo Completo
- [ ] **Testar todos os cenários da especificação**
  - Fluxo completo de criação → aprovação final
  - Todos os perfis e permissões
  - Transições de status automáticas
  - Modais e validações

#### 4.2 Notificações (Segunda Fase)
- [ ] **Preparar estrutura para notificações**
  - Documentar pontos de integração
  - Estrutura de templates
  - Configuração de e-mail

## 📅 Cronograma Refatorado

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

## 🔧 Detalhes Técnicos Específicos

### **Arquitetura Definida**
- **Controller único**: `PppController.php` com todos os métodos
- **Validação**: `StorePppRequest.php` para create e update
- **Services**: Centralização da lógica de negócio
- **Views**: Uso de partials para formulários complexos
- **Modals**: Estruturadas, claras e reutilizáveis

### **Tabela de Status Completa**
| ID | Nome                  | Uso Específico |
|----|----------------------|----------------|
| 1  | rascunho             | PPP em criação |
| 2  | aguardando_aprovacao | Enviado para aprovação |
| 3  | em_avaliacao         | Gestor visualizando |
| 4  | aguardando_correcao  | Correção solicitada |
| 5  | em_correcao          | Usuário corrigindo |
| 6  | aprovado_final       | Aprovado pelo DAF |
| 7  | cancelado            | Reprovado/Cancelado |
| 8  | aprovado_direx       | Aprovado pela Secretária |

### **Perfis e Permissões Detalhadas**
- **Admin**: Acesso total ao sistema
- **DAF**: PPPs próprios + SUPEX/DOM/DOE
- **Gestores**: PPPs próprios + subordinados
- **Usuário comum**: Apenas PPPs próprios
- **Secretária**: Visualização PCA + aprovações DIREX
- **Usuário externo**: Sem acesso (futuro)

## 📊 Critérios de Aceitação Específicos

### **Funcionalidades Obrigatórias**
- [ ] Formulário inicia apenas com card azul
- [ ] Animação profissional na transição
- [ ] Status automático `em_avaliacao` para gestores
- [ ] Modais específicas para cada ação
- [ ] Soft delete com comentário obrigatório
- [ ] Tabela PCA com layout tipo Excel
- [ ] Campo dinâmico "Anos restantes de vigência"
- [ ] Hierarquia de aprovação funcional
- [ ] Perfil secretária com funcionalidades específicas

### **Validações Críticas**
- [ ] Todos os campos obrigatórios validados
- [ ] Comentários obrigatórios onde especificado
- [ ] Bloqueio de edição após reprovação
- [ ] Permissões por perfil funcionando
- [ ] Transições de status corretas

## 🚨 Riscos e Dependências

### **Riscos Técnicos Específicos**
- **Complexidade da animação**: Pode impactar performance
- **Lógica de hierarquia**: Requer dados LDAP precisos
- **Tabela PCA tipo Excel**: Complexidade de interface
- **Campo dinâmico de anos**: Validações complexas

### **Dependências Críticas**
- Dados de hierarquia atualizados no LDAP
- Definição precisa do perfil "Secretária"
- Validação das regras de negócio com usuários
- Ambiente de teste com dados reais

## 📝 Observações Importantes

1. **Aderência Total**: Este plano segue rigorosamente a especificação em `project_rules.md`

2. **Priorização**: Funcionalidades core da especificação têm prioridade sobre melhorias gerais

3. **Validação Contínua**: Cada funcionalidade deve ser validada contra a especificação

4. **Documentação**: Manter `project_rules.md` atualizado com implementações

5. **Flexibilidade**: Notificações ficam para segunda fase conforme especificado

---

**Data de Criação**: Janeiro de 2025  
**Versão**: 3.0 (Refatorada baseada em project_rules.md)  
**Responsável**: Equipe de Desenvolvimento PCA  
**Próxima Revisão**: 20 de Janeiro de 2025  
**Referência**: project_rules.md - Especificação Funcional do Sistema