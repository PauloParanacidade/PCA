# PCA - Sistema de Planejamento de Contratações Anual

## Especificação Funcional do Sistema

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
* **Botão Cancelar**: redireciona para a dashboard

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
    * Se for DAF, status vai para **aprovado_final**, adicionando PPP à **tabela PCA** com ID incremental
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

### 8. Tabela PCA (Planejamento de Contratações Anual)
* Contém todos os PPPs com status **aprovado_final**
* Cada entrada recebe **ID incremental** após aprovação DAF
* Campos:
  * Todos os campos do `form.blade.php`
  * `id` como primeira coluna
* Layout:
  * Funciona como uma planilha Excel com:
    * Títulos congelados no topo
    * Colunas "id" e "nome_item" fixadas na esquerda
  * Mostra **total da coluna Valor total estimado (exercício)**
  * Mostra também o que foi de cada "Origem do recurso": Paranacidade, FDU e BID/FDU

### 9. Campo "Valor se +1 exercício" (Card Verde)
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

### 10. Permissões e Hierarquia

#### 10.1 Perfis e Acesso
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
  * Pode visualizar todos os PPPs na tabela PCA
  * Botões disponíveis ao final da linha:
    * **Aprovar todas**
    * **Aprovar individualmente**
    * **Gerar PDF**
    * **Gerar Planilha Excel**
  * Aprovações feitas por este perfil mudam o status para **aprovado_direx**
  * Também pode **criar PPPs normalmente**, como qualquer funcionário

#### 10.2 Campo `manager`
* Utilizado para **identificar o gestor imediato** e seu setor
* Extraído no momento do login
* Usado para **definir o próximo avaliador** do PPP
* Exceção:
  * Quando o avaliador for SUPEX, DOM ou DOE → encaminha para DAF, ignorando `manager`

### 11. Estrutura Técnica e Arquitetura
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

## Status do Sistema PPP

Baseado no arquivo `PPPStatusSeeder.php`, o sistema possui os seguintes status:

### Status Principais
| ID | Nome | Slug | Descrição | Cor |
|----|------|------|-----------|-----|
| 1 | Rascunho | `rascunho` | PPP em elaboração pelo usuário | Cinza (#6c757d) |
| 2 | Aguardando Aprovação | `aguardando_aprovacao` | PPP enviado para aprovação | Amarelo (#ffc107) |
| 3 | Em Avaliação | `em_avaliacao` | PPP sendo avaliado pelo gestor | Azul (#007bff) |
| 4 | Aguardando Correção | `aguardando_correcao` | PPP retornado para correção | Laranja (#fd7e14) |
| 5 | Em Correção | `em_correcao` | PPP sendo corrigido pelo usuário | Roxo claro (#e83e8c) |
| 6 | Aprovado Final | `aprovado_final` | PPP aprovado pelo DAF | Verde (#28a745) |
| 7 | Cancelado | `cancelado` | PPP cancelado | Vermelho (#dc3545) |

### Status Adicionais (DIREX e Conselho)
| ID | Nome | Slug | Descrição | Cor |
|----|------|------|-----------|-----|
| 8 | Aguardando Conselho | `aguardando_conselho` | PPP aguardando aprovação do Conselho | Azul escuro (#17a2b8) |
| 9 | Em Análise Conselho | `em_analise_conselho` | PPP sendo analisado pelo Conselho | Índigo (#6610f2) |
| 10 | Aprovado Conselho | `aprovado_conselho` | PPP aprovado pelo Conselho | Roxo (#6f42c1) |

### Regras de Transição

#### Aprovação e Reprovação
- **Métodos `aprovar()` e `reprovar()`** aceitam PPPs com status:
  - Status 2 (`aguardando_aprovacao`) - PPP ainda não visualizado pelo gestor
  - Status 3 (`em_avaliacao`) - PPP já visualizado pelo gestor

#### Visualização por Gestores
- Quando um gestor **visualiza** um PPP com status `aguardando_aprovacao` (2), o status é automaticamente alterado para `em_avaliacao` (3)
- Visualizações subsequentes mantêm o status `em_avaliacao` (3)

#### Exceção da Secretária
- Quando a secretária utiliza "Validar e Encaminhar", o PPP é aprovado diretamente conforme lógica específica no `PppService`

### 13. Notificações (🚧 Segunda fase do desenvolvimento)
* Toda mudança de status deverá gerar notificação por e-mail aos envolvidos
  (Será implementado futuramente)

---

Esse documento pode ser salvo como `ppp-especificacao.md` e atualizado conforme novas diretrizes.

Sempre que um aspecto do projeto for alterado, ampliado ou implementado **o texto anterior será mantido com uma sinalização indicando o que já foi realizado**, e o `.md` será atualizado e reanalisado para garantir aderência total ao sistema.
