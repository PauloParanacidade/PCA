<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ClcService
{
    /**
     * Identifica o coordenador do CLC
     * Busca o primeiro usuário que tenha no Manager uma pessoa com sigla CLC
     */
    public function identificarCoordenadorClc(): ?User
    {
        try {
            Log::info('🔍 ClcService.identificarCoordenadorClc() - INICIANDO');
            
            // Cache para evitar múltiplas consultas
            $cacheKey = 'coordenador_clc';
            
            return Cache::remember($cacheKey, 3600, function () {
                // Buscar usuários ativos do departamento CLC
                $usuarios = User::where('active', true)
                    ->where('department', 'CLC')
                    ->select('id', 'name', 'manager', 'department')
                    ->get();
                
                Log::info('🔍 Total de usuários do departamento CLC encontrados', [
                    'total' => $usuarios->count()
                ]);
                
                // Procurar pelo coordenador (usuário com role gestor ou que seja o chefe)
                foreach ($usuarios as $usuario) {
                    // Verificar se tem role de gestor ou se é o Aluisio (coordenador conhecido)
                    if ($usuario->hasRole('gestor') || stripos($usuario->name, 'Aluisio Clementino Soares') !== false) {
                        Log::info('✅ Coordenador CLC identificado', [
                            'usuario_id' => $usuario->id,
                            'usuario_nome' => $usuario->name,
                            'departamento' => $usuario->department
                        ]);
                        
                        return $usuario;
                    }
                }
                
                // Se não encontrou ninguém com role gestor, pega o primeiro do CLC
                if ($usuarios->isNotEmpty()) {
                    $coordenador = $usuarios->first();
                    Log::info('✅ Coordenador CLC identificado (primeiro do departamento)', [
                        'usuario_id' => $coordenador->id,
                        'usuario_nome' => $coordenador->name,
                        'departamento' => $coordenador->department
                    ]);
                    
                    return $coordenador;
                }
                
                Log::warning('❌ Coordenador CLC não encontrado');
                return null;
            });
            
        } catch (\Throwable $ex) {
            Log::error('❌ Erro ao identificar coordenador CLC: ' . $ex->getMessage());
            return null;
        }
    }
    
    /**
     * Limpa o cache do coordenador CLC
     */
    public function limparCacheCoordenadorClc(): void
    {
        Cache::forget('coordenador_clc');
        Log::info('🗑️ Cache do coordenador CLC limpo');
    }
    
    /**
     * Verifica se um usuário é o coordenador CLC
     */
    public function ehCoordenadorClc(User $usuario): bool
    {
        $coordenador = $this->identificarCoordenadorClc();
        return $coordenador && $coordenador->id === $usuario->id;
    }
    
    /**
     * Atribui a role 'clc' ao coordenador identificado
     */
    public function atribuirRoleClc(): bool
    {
        try {
            $coordenador = $this->identificarCoordenadorClc();
            
            if (!$coordenador) {
                Log::warning('❌ Não foi possível atribuir role CLC: coordenador não encontrado');
                return false;
            }
            
            // Verificar se já possui a role
            if ($coordenador->hasRole('clc')) {
                Log::info('ℹ️ Usuário já possui role CLC', [
                    'usuario_id' => $coordenador->id,
                    'usuario_nome' => $coordenador->name
                ]);
                return true;
            }
            
            // Buscar a role CLC
            $roleClc = \App\Models\Role::where('name', 'clc')->first();
            
            if (!$roleClc) {
                Log::error('❌ Role CLC não encontrada no sistema');
                return false;
            }
            
            // Atribuir a role
            $coordenador->roles()->attach($roleClc->id);
            
            Log::info('✅ Role CLC atribuída com sucesso', [
                'usuario_id' => $coordenador->id,
                'usuario_nome' => $coordenador->name
            ]);
            
            return true;
            
        } catch (\Throwable $ex) {
            Log::error('❌ Erro ao atribuir role CLC: ' . $ex->getMessage());
            return false;
        }
    }
}