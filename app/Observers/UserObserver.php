<?php
namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function creating(User $user)
    {
        // Se o usuário estiver sendo criado manualmente (sem guid/domain), define como externo
        if (empty($user->guid) && empty($user->domain)) {
            $user->isExterno = true;
            Log::info('Usuário externo criado', ['email' => $user->email]);
        } else {
            $user->isExterno = false;
            Log::info('Usuário LDAP criado', ['email' => $user->email]);
        }

        // Garante que todos os novos usuários estejam ativos por padrão
        if (! isset($user->active)) {
            $user->active = true;
        }
    }

    public function created(User $user)
    {
        // $userRole = Role::where('name', 'user')->first();
        // if ($userRole) {
        //     $user->roles()->attach($userRole->id);
        //     Log::info('Papel "user" atribuído ao usuário', ['email' => $user->email]);
        // }
        Log::info('UserObserver@created called for user', ['email' => $user->email, 'id' => $user->id]);
    }
}
