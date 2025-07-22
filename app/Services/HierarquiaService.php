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
                'user_manager' => $usuario->manager ?? 'N/A',
                'user_department' => $usuario->department ?? 'N/A'
            ]);

            // ✅ NOVA EXCEÇÃO: Verificar se o usuário é DAF, DOM, DOE, SUPEX ou Secretária
            if ($this->verificarSeEhPerfilEspecialParaDirex($usuario)) {
                Log::info('✅ Usuário identificado como perfil especial - Encaminhando para Secretária', [
                    'user_id' => $usuario->id,
                    'user_name' => $usuario->name,
                    'user_department' => $usuario->department ?? 'N/A'
                ]);
                
                // Buscar diretamente por role 'secretaria'
                $secretaria = User::whereHas('roles', function ($query) {
                    $query->where('name', 'secretaria');
                })->where('active', true)->first();
                
                if ($secretaria) {
                    Log::info('✅ Secretária encontrada para avaliação DIREX', [
                        'secretaria_id' => $secretaria->id,
                        'secretaria_nome' => $secretaria->name
                    ]);
                    return $secretaria;
                } else {
                    Log::error('❌ Secretária não encontrada no sistema');
                    throw new Exception('Secretária não encontrada no sistema');
                }
            }

            // Lógica normal para outros usuários
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
     * Verifica se o usuário é DAF, DOM, DOE, SUPEX ou Secretária
     * Estes perfis devem ter seus PPPs avaliados pela Secretária para DIREX
     */
    private function verificarSeEhPerfilEspecialParaDirex(User $usuario): bool
    {
        // Verificar por role secretaria
        if ($usuario->hasRole('secretaria')) {
            return true;
        }
        
        // Verificar por role daf
        if ($usuario->hasRole('daf')) {
            return true;
        }
        
        // Verificar por department (sigla da área)
        $department = strtoupper($usuario->department ?? '');
        $areasEspeciais = ['DAF', 'DOM', 'DOE', 'SUPEX'];
        
        return in_array($department, $areasEspeciais);
    }
    
    /**
     * Obtém a Secretária da DIREX/Conselho
     */
    private function obterSecretaria(): ?User
    {
        // Primeiro, tentar encontrar por role 'secretaria'
        $secretaria = User::whereHas('roles', function ($query) {
            $query->where('name', 'secretaria');
        })->where('active', true)->first();
        
        if ($secretaria) {
            return $secretaria;
        }
        
        // Fallback: buscar por nome específico (Vera Morais Ferreira)
        $secretaria = User::where('name', 'like', '%Vera Morais Ferreira%')
            ->where('active', true)
            ->first();
            
        return $secretaria;
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
