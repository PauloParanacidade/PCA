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
     * Obtém a Secretária da DIREX/Conselho
     */
    public function obterSecretaria(): ?User
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
     * Verifica se o usuário é gestor de outro usuário baseado na hierarquia LDAP
     */
    public function ehGestorDe(User $gestor, User $subordinado): bool
    {
        Log::info('🔍 DEBUG ehGestorDe - INICIANDO', [
            'gestor_id' => $gestor->id,
            'gestor_name' => $gestor->name,
            'gestor_roles' => $gestor->roles->pluck('name')->toArray(),
            'subordinado_id' => $subordinado->id,
            'subordinado_name' => $subordinado->name,
            'subordinado_manager' => $subordinado->manager
        ]);

        // PRIMEIRA VERIFICAÇÃO: Roles especiais (admin, daf, secretaria)
        if ($gestor->hasRole(['admin', 'daf', 'secretaria'])) {
            Log::info('✅ DEBUG ehGestorDe - ROLE ESPECIAL APROVADA', [
                'gestor_role' => $gestor->roles->pluck('name')->toArray(),
                'resultado' => true
            ]);
            return true;
        }

        // SEGUNDA VERIFICAÇÃO: Exceções DOM, SUPEX, DOE, SECRETARIA
        // DOM, SUPEX, DOE e SECRETARIA podem gerenciar PPPs de subordinados até 2 níveis hierárquicos
        if ($gestor->hasRole(['dom', 'supex', 'doe', 'secretaria'])) {
            Log::info('🔍 DEBUG ehGestorDe - Verificando exceção DOM/SUPEX/DOE/SECRETARIA', [
                'gestor_role' => $gestor->roles->pluck('name')->toArray()
            ]);
            
            // Verificar se é gestor direto ou indireto (até 2 níveis)
            $ehGestorHierarquico = $this->verificarHierarquiaMultiNivel($gestor, $subordinado, 2);
            
            Log::info('✅ DEBUG ehGestorDe - EXCEÇÃO DOM/SUPEX/DOE', [
                'eh_gestor_hierarquico' => $ehGestorHierarquico,
                'resultado_final' => $ehGestorHierarquico
            ]);
            
            return $ehGestorHierarquico;
        }

        // TERCEIRA VERIFICAÇÃO: Hierarquia normal (1 nível)
        if (empty($subordinado->manager)) {
            Log::info('❌ DEBUG ehGestorDe - SUBORDINADO SEM MANAGER', [
                'resultado' => false
            ]);
            return false;
        }

        $nomeGestorEsperado = $this->extrairNomeDoManager($subordinado->manager);
        
        if (empty($nomeGestorEsperado)) {
            Log::info('❌ DEBUG ehGestorDe - NOME GESTOR ESPERADO VAZIO', [
                'manager_dn' => $subordinado->manager,
                'resultado' => false
            ]);
            return false;
        }

        $ehGestorDireto = stripos($nomeGestorEsperado, $gestor->name) !== false;
        
        Log::info('🔍 DEBUG ehGestorDe - Comparação direta', [
            'nome_gestor_esperado' => $nomeGestorEsperado,
            'nome_gestor_atual' => $gestor->name,
            'eh_gestor_direto' => $ehGestorDireto
        ]);
        
        if ($ehGestorDireto) {
            Log::info('✅ DEBUG ehGestorDe - HIERARQUIA DIRETA APROVADA', [
                'resultado_final' => true
            ]);
            return true;
        }

        Log::info('❌ DEBUG ehGestorDe - HIERARQUIA NEGADA', [
            'resultado_final' => false
        ]);
        return false;
    }

    /**
     * Extrai o nome do gestor a partir do DN do LDAP
     */
    private function extrairNomeDoManager(string $managerDN): ?string
    {
        if (empty($managerDN)) {
            return null;
        }
        
        // Extrair nome do formato: CN=Nome do Gestor,OU=Area,DC=domain,DC=com
        if (preg_match('/CN=([^,]+)/', $managerDN, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }

    /**
     * Verifica hierarquia em múltiplos níveis
     */
    private function verificarHierarquiaMultiNivel(User $gestor, User $subordinado, int $maxNiveis = 2): bool
    {
        $usuarioAtual = $subordinado;
        
        for ($nivel = 1; $nivel <= $maxNiveis; $nivel++) {
            if (empty($usuarioAtual->manager)) {
                Log::info('🔍 DEBUG verificarHierarquiaMultiNivel - Sem manager no nível', [
                    'nivel' => $nivel,
                    'usuario' => $usuarioAtual->name
                ]);
                break;
            }
            
            $nomeGestorEsperado = $this->extrairNomeDoManager($usuarioAtual->manager);
            
            if (empty($nomeGestorEsperado)) {
                Log::info('🔍 DEBUG verificarHierarquiaMultiNivel - Nome gestor vazio no nível', [
                    'nivel' => $nivel,
                    'manager_dn' => $usuarioAtual->manager
                ]);
                break;
            }
            
            // Verificar se o gestor atual é o gestor esperado neste nível
            if (stripos($nomeGestorEsperado, $gestor->name) !== false) {
                Log::info('✅ DEBUG verificarHierarquiaMultiNivel - ENCONTRADO', [
                    'nivel' => $nivel,
                    'gestor_encontrado' => $nomeGestorEsperado,
                    'gestor_procurado' => $gestor->name
                ]);
                return true;
            }
            
            // Buscar o próximo nível hierárquico
            $proximoGestor = User::where('name', 'LIKE', '%' . $nomeGestorEsperado . '%')->first();
            
            if (!$proximoGestor) {
                Log::info('🔍 DEBUG verificarHierarquiaMultiNivel - Gestor não encontrado no BD', [
                    'nivel' => $nivel,
                    'nome_procurado' => $nomeGestorEsperado
                ]);
                break;
            }
            
            Log::info('🔍 DEBUG verificarHierarquiaMultiNivel - Subindo nível', [
                'nivel_atual' => $nivel,
                'de' => $usuarioAtual->name,
                'para' => $proximoGestor->name
            ]);
            
            $usuarioAtual = $proximoGestor;
        }
        
        return false;
    }

    /**
     * Obtém o próximo gestor considerando regras especiais (ex: SUPEX → DAF)
     */
    public function obterGestorComTratamentoEspecial($user): ?User
    {
        // ✅ MELHORAR a validação do usuário
        if (is_numeric($user)) {
            $usuario = User::find($user);
        } elseif ($user instanceof User) {
            $usuario = $user;
        } else {
            Log::warning('❌ Parâmetro inválido em obterGestorComTratamentoEspecial', [
                'user_type' => gettype($user),
                'user_value' => $user
            ]);
            return null;
        }
    
        if (!$usuario) {
            Log::warning('❌ Usuário não encontrado em obterGestorComTratamentoEspecial');
            return null;
        }
    
        Log::info('🔍 DEBUG obterGestorComTratamentoEspecial - Iniciando', [
            'user_id' => $usuario->id,
            'user_name' => $usuario->name,
            'user_department' => $usuario->department ?? 'N/A'
        ]);
    
        // ✅ CORREÇÃO: Este método deve sempre retornar o DAF para áreas especiais
        // Buscar usuários com role 'daf' que tenham department 'DAF'
        $daf = User::where('active', true)
            ->where(function($query) {
                $query->where('department', 'DAF')
                      ->orWhere('department', 'daf');
            })
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['daf', 'admin']);
            })
            ->first();
            
        if ($daf) {
            Log::info('✅ DAF encontrado para tratamento especial', [
                'daf_id' => $daf->id,
                'daf_name' => $daf->name,
                'daf_department' => $daf->department
            ]);
            return $daf;
        }
        
        Log::warning('❌ DAF não encontrado para tratamento especial');
        throw new Exception('DAF não encontrado no sistema para aprovação de áreas especiais');
    }

    /**
     * Obtém a árvore hierárquica de usuários subordinados ao usuário fornecido
     * Retorna array de IDs dos usuários que estão na hierarquia
     */
    public function obterArvoreHierarquica(User $user): array
    {
        try {
            // OTIMIZAÇÃO: Cache por 5 minutos para evitar recálculos desnecessários
            $cacheKey = "arvore_hierarquica_user_{$user->id}";
            
            return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($user) {
                Log::info('🌳 HierarquiaService.obterArvoreHierarquica() - INICIANDO', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_department' => $user->department ?? 'N/A'
                ]);

                $usuariosArvore = [$user->id]; // Incluir o próprio usuário
                
                // Buscar todos os usuários que têm este usuário como gestor (direto ou indireto)
                $subordinados = $this->buscarSubordinados($user);
                
                foreach ($subordinados as $subordinado) {
                    $usuariosArvore[] = $subordinado->id;
                    
                    // Buscar subordinados dos subordinados (recursivo até 3 níveis)
                    $subSubordinados = $this->buscarSubordinados($subordinado, 2);
                    foreach ($subSubordinados as $subSubordinado) {
                        if (!in_array($subSubordinado->id, $usuariosArvore)) {
                            $usuariosArvore[] = $subSubordinado->id;
                        }
                    }
                }
                
                Log::info('✅ Árvore hierárquica obtida com sucesso', [
                    'total_usuarios' => count($usuariosArvore),
                    'usuarios_ids' => $usuariosArvore
                ]);
                
                return array_unique($usuariosArvore);
            });
            
        } catch (\Throwable $ex) {
            Log::error('❌ Erro ao obter árvore hierárquica: ' . $ex->getMessage());
            return [$user->id]; // Retorna pelo menos o próprio usuário
        }
    }

    /**
     * Busca subordinados diretos de um usuário - VERSÃO OTIMIZADA
     */
    private function buscarSubordinados(User $gestor, int $maxNiveis = 1): array
    {
        $subordinados = [];
        
        try {
            // OTIMIZAÇÃO: Buscar usuários que têm este gestor no campo manager de uma vez só
            $usuarios = User::where('active', true)
                ->whereNotNull('manager')
                ->with('roles') // Carregar roles para evitar N+1
                ->get();
                
            foreach ($usuarios as $usuario) {
                if ($this->ehGestorDeOtimizado($gestor, $usuario)) {
                    $subordinados[] = $usuario;
                }
            }
            
            Log::info('🔍 Subordinados encontrados', [
                'gestor_id' => $gestor->id,
                'gestor_name' => $gestor->name,
                'total_subordinados' => count($subordinados),
                'subordinados_ids' => array_map(fn($u) => $u->id, $subordinados)
            ]);
            
        } catch (\Throwable $ex) {
            Log::error('❌ Erro ao buscar subordinados: ' . $ex->getMessage());
        }
        
        return $subordinados;
    }

    /**
     * Versão otimizada do ehGestorDe que reduz logs desnecessários
     */
    private function ehGestorDeOtimizado(User $gestor, User $subordinado): bool
    {
        // PRIMEIRA VERIFICAÇÃO: Roles especiais (admin, daf, secretaria)
        if ($gestor->hasRole(['admin', 'daf', 'secretaria'])) {
            return true;
        }

        // SEGUNDA VERIFICAÇÃO: Exceções DOM, SUPEX, DOE, SECRETARIA
        if ($gestor->hasRole(['dom', 'supex', 'doe', 'secretaria'])) {
            return $this->verificarHierarquiaMultiNivel($gestor, $subordinado, 2);
        }

        // TERCEIRA VERIFICAÇÃO: Hierarquia normal (1 nível)
        if (empty($subordinado->manager)) {
            return false;
        }

        $nomeGestorEsperado = $this->extrairNomeDoManager($subordinado->manager);
        
        if (empty($nomeGestorEsperado)) {
            return false;
        }

        return stripos($nomeGestorEsperado, $gestor->name) !== false;
     }

    /**
     * Limpa o cache da árvore hierárquica de um usuário específico
     */
    public function limparCacheArvoreHierarquica(User $user): void
    {
        $cacheKey = "arvore_hierarquica_user_{$user->id}";
        \Illuminate\Support\Facades\Cache::forget($cacheKey);
        
        Log::info('🗑️ Cache da árvore hierárquica limpo', [
            'user_id' => $user->id,
            'cache_key' => $cacheKey
        ]);
    }

    /**
     * Limpa todo o cache de árvores hierárquicas
     */
    public function limparTodoCacheArvoreHierarquica(): void
    {
        $usuarios = \App\Models\User::where('active', true)->pluck('id');
        
        foreach ($usuarios as $userId) {
            $cacheKey = "arvore_hierarquica_user_{$userId}";
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }
        
        Log::info('🗑️ Todo cache de árvores hierárquicas limpo', [
            'total_usuarios' => $usuarios->count()
        ]);
    }

    /**
     * Limpa o cache quando há mudanças na estrutura hierárquica
     * Deve ser chamado quando usuários são criados, editados ou desativados
     */
    public function invalidarCacheHierarquia(?User $usuarioAfetado = null): void
    {
        if ($usuarioAfetado) {
            // Limpar cache do usuário afetado
            $this->limparCacheArvoreHierarquica($usuarioAfetado);
            
            // Limpar cache de todos os usuários que podem ter este usuário em sua árvore
            // Para simplificar, vamos limpar todo o cache quando há mudanças
            $this->limparTodoCacheArvoreHierarquica();
        } else {
            // Limpar todo o cache
            $this->limparTodoCacheArvoreHierarquica();
        }
    }
 }
