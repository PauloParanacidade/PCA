<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class HierarquiaService
{
    /**
     * Obtém o próximo gestor na hierarquia baseado no campo manager do LDAP
     */
    public function obterProximoGestor($usuario): ?User
    {
        // Se recebeu um ID, buscar o usuário
        if (is_numeric($usuario)) {
            $usuario = User::find($usuario);
        }
        
        if (!$usuario) {
            Log::warning('❌ Usuário não encontrado para obter próximo gestor');
            return null;
        }
        
        Log::info('🔍 HierarquiaService.obterProximoGestor() - INICIANDO', [
            'user_id' => $usuario->id,
            'user_name' => $usuario->name,
            'user_manager' => $usuario->manager ?? 'N/A'
        ]);
        
        // Extrair o gestor do campo manager (formato LDAP)
        $managerDN = $usuario->manager;
        
        if (!$managerDN) {
            Log::warning('❌ Usuário não possui gestor definido', ['user_id' => $usuario->id]);
            return null;
        }
        
        Log::info('🔍 Manager DN encontrado', ['manager_dn' => $managerDN]);
        
        // Extrair o nome do gestor do Distinguished Name (DN)
        // Formato: CN=Nome do Gestor,OU=Sigla da Área,DC=domain,DC=com
        if (preg_match('/CN=([^,]+),OU=([^,]+)/', $managerDN, $matches)) {
            $nomeGestor = trim($matches[1]);
            $siglaAreaGestor = trim($matches[2]);
            
            Log::info('✅ Dados extraídos do DN', [
                'nome_gestor' => $nomeGestor,
                'sigla_area' => $siglaAreaGestor
            ]);
            
            // Buscar o gestor pelo nome
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
        } else {
            Log::warning('❌ Formato do manager DN não reconhecido', [
                'user_id' => $usuario->id,
                'manager_dn' => $managerDN
            ]);
        }
        
        return null;
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