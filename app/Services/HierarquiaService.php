<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class HierarquiaService
{
    /**
     * Obtém o próximo gestor na hierarquia
     * Por enquanto, implementação simples - pode ser expandida conforme necessário
     */
    public function obterProximoGestor(User $usuario): ?User
    {
        try {
            // Implementação temporária: buscar usuários com role 'gestor', 'daf' ou 'admin'
            // que não sejam o usuário atual
            $proximoGestor = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['gestor', 'daf', 'admin']);
            })
            ->where('id', '!=', $usuario->id)
            ->first();
            
            return $proximoGestor;
            
        } catch (\Exception $e) {
            Log::error('Erro ao obter próximo gestor: ' . $e->getMessage());
            return null;
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