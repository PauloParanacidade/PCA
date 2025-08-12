<?php

namespace App\Policies;

use App\Models\PcaPpp;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PppPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar o PPP.
     */
    public function view(User $user, PcaPpp $ppp): bool
    {
        // Criador sempre pode visualizar
        if ($ppp->user_id === $user->id) {
            return true;
        }

        // Usuários com roles especiais podem visualizar
        if ($user->hasAnyRole(['admin', 'daf', 'gestor', 'secretaria', 'supex', 'clc'])) {
            return true;
        }

        return false;
    }

    /**
     * Determina se o usuário pode atualizar o PPP.
     */
    public function update(User $user, PcaPpp $ppp): bool
    {
        // Admin sempre pode editar
        if ($user->hasRole('admin')) {
            return true;
        }

        // NOVA REGRA: Apenas SUPEX e DAF podem editar PPPs após envio (status 4 e 5)
        if (in_array($ppp->status_id, [4, 5])) { // aguardando_correcao, em_correcao
            return $user->hasAnyRole(['supex', 'daf']);
        }

        // Para outros status, verificar se é o criador ou tem permissão
        if ($ppp->user_id === $user->id) {
            // Criador pode editar apenas no status rascunho (1)
            return $ppp->status_id === 1;
        }

        // Gestores podem editar PPPs de outros em determinados status
        if ($user->hasAnyRole(['daf', 'gestor', 'secretaria', 'supex', 'clc'])) {
            // Podem editar nos status: aguardando_aprovacao, em_avaliacao, cancelado, aguardando_direx, direx_avaliando
            return in_array($ppp->status_id, [2, 3, 7, 8, 9]);
        }

        return false;
    }

    /**
     * Determina se o usuário pode deletar o PPP.
     */
    public function delete(User $user, PcaPpp $ppp): bool
    {
        // Apenas admin pode deletar
        if ($user->hasRole('admin')) {
            return true;
        }

        // Criador pode deletar apenas se estiver em rascunho
        if ($ppp->user_id === $user->id && $ppp->status_id === 1) {
            return true;
        }

        return false;
    }

    /**
     * Determina se o usuário pode aprovar o PPP.
     */
    public function approve(User $user, PcaPpp $ppp): bool
    {
        // Verificar se tem permissão para aprovar
        if (!$user->hasAnyRole(['admin', 'daf', 'gestor', 'secretaria', 'clc'])) {
            return false;
        }

        // Verificar se o PPP está no status correto
        if (!in_array($ppp->status_id, [2, 3])) { // aguardando_aprovacao, em_avaliacao
            return false;
        }

        // Verificar se é o gestor responsável
        return $ppp->gestor_atual_id === $user->id;
    }

    /**
     * Determina se o usuário pode reprovar o PPP.
     */
    public function reject(User $user, PcaPpp $ppp): bool
    {
        return $this->approve($user, $ppp); // Mesmas regras da aprovação
    }

    /**
     * Determina se o usuário pode solicitar correção do PPP.
     */
    public function requestCorrection(User $user, PcaPpp $ppp): bool
    {
        // Verificar se tem permissão para solicitar correção
        if (!$user->hasAnyRole(['admin', 'daf', 'gestor', 'secretaria', 'clc'])) {
            return false;
        }

        // Verificar se o PPP está no status correto
        if (!in_array($ppp->status_id, [2, 3])) { // aguardando_aprovacao, em_avaliacao
            return false;
        }

        return true;
    }

    /**
     * Determina se o usuário pode responder a correção do PPP.
     */
    public function respondCorrection(User $user, PcaPpp $ppp): bool
    {
        // NOVA REGRA: Apenas SUPEX e DAF podem responder correções
        if (!$user->hasAnyRole(['admin', 'supex', 'daf'])) {
            return false;
        }

        // Verificar se é o responsável pela correção
        if ($ppp->gestor_atual_id !== $user->id) {
            return false;
        }

        // Verificar se o PPP está no status correto
        return in_array($ppp->status_id, [4, 5]); // aguardando_correcao, em_correcao
    }
}