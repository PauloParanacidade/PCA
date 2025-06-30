# Fluxo de Interface do Meus PPPs
## 1. Visualizar PPP

Ao clicar em **Visualizar** na tabela ou **em qualquer parte da linha do PPP**, o PPP será aberto com os campos **bloqueados para edição**.

### 1.1 Para o usuário inicial

- Botões disponíveis:
  - **Histórico**
  - **Retornar**

- Comportamentos dos botões:
  - **Histórico**: veja _"na tabela, ao clicar em histórico"_.
  - **Retornar**: retorna para a tabela.

### 1.2 Para qualquer gestor

- Botões disponíveis:
  - **Histórico**
  - **Aprovar**
  - **Solicitar correção**
  - **Editar**
  - **Reprovar**
  - **Retornar**

- Comportamentos dos botões:

  - **Histórico**: veja _"na tabela, ao clicar em histórico"_.

  - **Aprovar**:
    - Abre uma modal de comentário (opcional).
    - O comentário é gravado no histórico.
    - O PPP avança para o próximo nível da hierarquia.
    - O status é alterado.
    - Retorna para a tabela exibindo a mensagem:  
      `Aprovado com sucesso`.

  - **Solicitar correção**:
    - Abre uma modal de comentário **obrigatório**.
    - O gestor descreve o que deve ser corrigido.
    - O comentário é gravado no histórico.
    - O status é alterado.
    - O usuário do nível hierárquico anterior volta a ter acesso editável ao PPP.
    - Retorna para a tabela com a mensagem:  
      `Solicitado correção do PPP`.

  - **Editar**: veja _"na tabela, ao clicar em editar"_.

  - **Reprovar**:
    - O PPP fica bloqueado para futuras edições (somente visualização).
    - Abre modal de comentário **obrigatório** para descrever o motivo da reprovação.
    - O status é alterado.
    - Retorna para a tabela com a mensagem:  
      `PPP foi reprovado e não será possível editá-lo`.

  - **Retornar**: retorna para a tabela.

---

## 2. Editar PPP

Ao clicar em **Editar** na tabela, o PPP será aberto com os campos **disponíveis para edição**.

### 2.1 Para o usuário inicial

- Botões disponíveis:
  - **Histórico**
  - **Salvar**
  - **Retornar**

- Fluxo após edição:
  - O botão **Salvar** aparece.
  - Ao clicar em salvar:
    - Abre modal de comentário **obrigatório**.
    - O PPP segue para o próximo nível da hierarquia.
    - O status é alterado.
    - Retorna para a tabela com a mensagem:  
      `Editado e Aprovado com sucesso`.

---

## 3. Histórico

Ao clicar em **Histórico** na tabela, será aberta uma **modal exibindo o histórico completo** do PPP.

---

## 4. Remover PPP

Ao clicar em **Remover** na tabela:

- Executa um **soft delete** (campo `deleted_at` será preenchido).
- Abre uma **modal de comentário obrigatório**.
- Botão disponível:  
  `Salvar mensagem e excluir definitivamente`.

- Ao confirmar:
  - Aparece um alerta em nova modal avisando que:
    - Essa ação **não tem os mesmos efeitos que reprovar**.
    - O PPP ainda permanecerá disponível para consultas futuras se reprovado.
    - Excluir definitivamente elimina o PPP do sistema permanentemente.

---

## 5. Notificações

- **Toda alteração de status** deve gerar um **e-mail automático** para todos os envolvidos até o momento.
