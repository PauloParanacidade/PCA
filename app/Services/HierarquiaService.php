<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class HierarquiaService
{
    /**
     * Obtém o próximo gestor na hierarquia baseado no campo manager do LDAP
     */
    public function obterProximoGestor($user): ?User
{
    try {
        $usuario = $user instanceof User
            ? $user
            : (is_numeric($user) ? User::find($user) : null);

        if (!$usuario) {
            Log::warning('❌ Usuário não encontrado para obter próximo gestor');
            throw new Exception('Usuário não encontrado para obter próximo gestor');
        }

        Log::info('🔍 HierarquiaService.obterProximoGestor() - INICIANDO', [
            'user_id' => $usuario->id,
            'user_name' => $usuario->name,
            'user_manager' => $usuario->manager ?? 'N/A'
        ]);

        $managerDN = $usuario->manager;

        if (!$managerDN) {
            Log::warning('❌ Usuário não possui gestor definido', ['user_id' => $usuario->id]);
            throw new Exception('Usuário não possui gestor definido');
        }

        Log::info('🔍 Manager DN encontrado', ['manager_dn' => $managerDN]);

        if (preg_match('/CN=([^,]+),OU=([^,]+)/', $managerDN, $matches)) {
            $nomeGestor = trim($matches[1]);
            $siglaAreaGestor = trim($matches[2]);

            Log::info('✅ Dados extraídos do DN', [
                'nome_gestor' => $nomeGestor,
                'sigla_area' => $siglaAreaGestor
            ]);

            $gestor = User::where('name', 'like', "%{$nomeGestor}%")
                         ->where('active', true)
                         ->first();

            if ($gestor) {
                Log::info('✅ Gestor encontrado na hierarquia', [
                    'usuario_id' => $usuario->id,
                    'gestor_id' => $gestor->id,
                    'gestor_nome' => $gestor->name,
                    'area_gestor' => $siglaAreaGestor
                ]);
                return $gestor;
            }

            Log::warning('❌ Gestor não encontrado na base de dados', [
                'user_id' => $usuario->id,
                'nome_gestor_extraido' => $nomeGestor,
                'area_gestor_extraida' => $siglaAreaGestor
            ]);
            throw new Exception('Gestor não encontrado na base de dados');
        }

        Log::warning('❌ Formato do manager DN não reconhecido', [
            'user_id' => $usuario->id,
            'manager_dn' => $managerDN
        ]);
        throw new Exception('Formato do manager DN não reconhecido');

    } catch (\Throwable $ex) {
        Log::error('Erro ao obter próximo gestor: ' . $ex->getMessage());
        throw $ex;
    }
}


    /**
     * Verifica se o usuário é gestor de outro usuário
     */
    public function ehGestorDe(User $gestor, User $subordinado): bool
    {
        // Implementação simples: gestores podem aprovar PPPs de usuários comuns
        return $gestor->hasAnyRole(['admin', 'daf', 'gestor']) &&
               !$subordinado->hasAnyRole(['admin', 'daf', 'gestor']);
    }
}
