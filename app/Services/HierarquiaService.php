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
     * OTIMIZADO: Reduz consultas e melhora performance
     */
    public function obterArvoreHierarquica(User $user): array
    {
        try {
            Log::info('🌳 HierarquiaService.obterArvoreHierarquica() - INICIANDO', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_department' => $user->department ?? 'N/A'
            ]);

            $usuariosArvore = [$user->id]; // Incluir o próprio usuário
            
            // OTIMIZAÇÃO: Buscar todos os usuários ativos de uma vez
            $todosUsuarios = User::where('active', true)
                ->whereNotNull('manager')
                ->select('id', 'name', 'manager', 'department')
                ->get();
            
            // OTIMIZAÇÃO: Criar mapa de usuários por manager para busca mais rápida
            $usuariosPorManager = [];
            foreach ($todosUsuarios as $usuario) {
                $managerNome = $this->extrairNomeDoManager($usuario->manager);
                if ($managerNome) {
                    if (!isset($usuariosPorManager[$managerNome])) {
                        $usuariosPorManager[$managerNome] = [];
                    }
                    $usuariosPorManager[$managerNome][] = $usuario;
                }
            }
            
            // Buscar subordinados diretos e indiretos (até 3 níveis)
            $subordinadosEncontrados = $this->buscarSubordinadosOtimizado($user, $todosUsuarios, $usuariosPorManager, 3);
            
            foreach ($subordinadosEncontrados as $subordinado) {
                if (!in_array($subordinado->id, $usuariosArvore)) {
                    $usuariosArvore[] = $subordinado->id;
                }
            }
            
            Log::info('✅ Árvore hierárquica obtida com sucesso (OTIMIZADA)', [
                'total_usuarios' => count($usuariosArvore),
                'usuarios_ids' => $usuariosArvore
            ]);
            
            return array_unique($usuariosArvore);
            
        } catch (\Throwable $ex) {
            Log::error('❌ Erro ao obter árvore hierárquica: ' . $ex->getMessage());
            return [$user->id]; // Retorna pelo menos o próprio usuário
        }
    }

    /**
     * Busca subordinados de forma otimizada (versão melhorada)
     */
    private function buscarSubordinadosOtimizado(User $gestor, $todosUsuarios, $usuariosPorManager, int $maxNiveis = 3): array
    {
        $subordinados = [];
        $processados = [];
        $fila = [$gestor];
        $nivel = 0;
        
        while (!empty($fila) && $nivel < $maxNiveis) {
            $nivel++;
            $filaNivel = $fila;
            $fila = [];
            
            foreach ($filaNivel as $usuarioAtual) {
                if (in_array($usuarioAtual->id, $processados)) {
                    continue;
                }
                
                $processados[] = $usuarioAtual->id;
                
                // Buscar subordinados diretos deste usuário
                $subordinadosDiretos = $this->encontrarSubordinadosDiretos($usuarioAtual, $todosUsuarios, $usuariosPorManager);
                
                foreach ($subordinadosDiretos as $subordinado) {
                    if (!in_array($subordinado->id, $processados)) {
                        $subordinados[] = $subordinado;
                        $fila[] = $subordinado; // Adicionar à fila para próximo nível
                    }
                }
            }
        }
        
        return $subordinados;
    }
    
    /**
     * Encontra subordinados diretos de um usuário
     */
    private function encontrarSubordinadosDiretos(User $gestor, $todosUsuarios, $usuariosPorManager): array
    {
        $subordinados = [];
        $nomeGestor = $gestor->name;
        
        // Buscar por nome exato ou similar no manager
        foreach ($todosUsuarios as $usuario) {
            if ($usuario->id === $gestor->id) {
                continue; // Pular o próprio gestor
            }
            
            $managerNome = $this->extrairNomeDoManager($usuario->manager);
            if ($managerNome && $this->nomesSaoSimilares($nomeGestor, $managerNome)) {
                $subordinados[] = $usuario;
            }
        }
        
        return $subordinados;
    }
    
    /**
     * Verifica se dois nomes são similares (para lidar com variações)
     */
    private function nomesSaoSimilares(string $nome1, string $nome2): bool
    {
        $nome1 = strtolower(trim($nome1));
        $nome2 = strtolower(trim($nome2));
        
        // Verificação exata
        if ($nome1 === $nome2) {
            return true;
        }
        
        // Verificação de similaridade (pelo menos 80% similar)
        similar_text($nome1, $nome2, $percent);
        return $percent >= 80;
    }
}
