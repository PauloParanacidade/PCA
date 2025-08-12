<?php

namespace App\Services\Hierarchy;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Construtor otimizado da árvore hierárquica organizacional
 * 
 * Responsável por:
 * - Construir árvore hierárquica completa de forma eficiente
 * - Detectar e prevenir ciclos hierárquicos
 * - Validar integridade da estrutura
 * - Otimizar consultas para reduzir N+1
 */
class HierarchyTreeBuilder
{
    private const MAX_HIERARCHY_DEPTH = 10;
    private const BATCH_SIZE = 100;
    
    /**
     * Constrói a árvore hierárquica completa
     */
    public function buildCompleteTree(): array
    {
        $startTime = microtime(true);
        
        Log::info('🌳 Iniciando construção da árvore hierárquica completa');
        
        // Carregar todos os usuários ativos de uma vez
        $users = $this->loadAllActiveUsers();
        
        if ($users->isEmpty()) {
            Log::warning('⚠️ Nenhum usuário ativo encontrado para construir árvore');
            return [];
        }
        
        // Construir mapa de usuários por nome para lookup rápido
        $usersByName = $this->buildUserNameMap($users);
        
        // Construir árvore usando algoritmo otimizado
        $tree = $this->buildTreeStructure($users, $usersByName);
        
        // Validar integridade da árvore
        $this->validateTreeIntegrity($tree);
        
        $duration = microtime(true) - $startTime;
        
        Log::info('✅ Árvore hierárquica construída com sucesso', [
            'duration' => round($duration, 3) . 's',
            'total_users' => $users->count(),
            'tree_nodes' => count($tree),
            'max_depth' => $this->calculateMaxDepth($tree)
        ]);
        
        return $tree;
    }
    
    /**
     * Constrói árvore de subordinados para um usuário específico
     */
    public function buildUserSubordinatesTree(int $userId, int $maxDepth = 3): array
    {
        $startTime = microtime(true);
        
        $user = User::with('roles')->find($userId);
        
        if (!$user) {
            Log::warning('⚠️ Usuário não encontrado para construir árvore de subordinados', [
                'user_id' => $userId
            ]);
            return [];
        }
        
        Log::info('👥 Construindo árvore de subordinados', [
            'user_id' => $userId,
            'user_name' => $user->name,
            'max_depth' => $maxDepth
        ]);
        
        $subordinates = $this->findSubordinatesRecursive($user, $maxDepth);
        
        $duration = microtime(true) - $startTime;
        
        Log::info('✅ Árvore de subordinados construída', [
            'user_id' => $userId,
            'subordinates_count' => count($subordinates),
            'duration' => round($duration, 3) . 's'
        ]);
        
        return $subordinates;
    }
    
    /**
     * Detecta ciclos na hierarquia
     */
    public function detectCycles(): array
    {
        $users = $this->loadAllActiveUsers();
        $cycles = [];
        $visited = [];
        $recursionStack = [];
        
        foreach ($users as $user) {
            if (!isset($visited[$user->id])) {
                $cycle = $this->detectCycleFromUser($user, $visited, $recursionStack, $users);
                if (!empty($cycle)) {
                    $cycles[] = $cycle;
                }
            }
        }
        
        if (!empty($cycles)) {
            Log::warning('🔄 Ciclos detectados na hierarquia', [
                'cycles_count' => count($cycles),
                'cycles' => $cycles
            ]);
        }
        
        return $cycles;
    }
    
    /**
     * Valida integridade da estrutura hierárquica
     */
    public function validateTreeIntegrity(array $tree): array
    {
        $issues = [];
        
        // Verificar ciclos
        $cycles = $this->detectCycles();
        if (!empty($cycles)) {
            $issues['cycles'] = $cycles;
        }
        
        // Verificar usuários órfãos (com manager inexistente)
        $orphans = $this->findOrphanUsers();
        if (!empty($orphans)) {
            $issues['orphans'] = $orphans;
        }
        
        // Verificar profundidade excessiva
        $deepNodes = $this->findExcessivelyDeepNodes($tree);
        if (!empty($deepNodes)) {
            $issues['excessive_depth'] = $deepNodes;
        }
        
        if (!empty($issues)) {
            Log::warning('⚠️ Problemas de integridade detectados na árvore hierárquica', $issues);
        } else {
            Log::info('✅ Integridade da árvore hierárquica validada com sucesso');
        }
        
        return $issues;
    }
    
    /**
     * Carrega todos os usuários ativos com informações necessárias
     */
    private function loadAllActiveUsers(): Collection
    {
        return User::with('roles:id,name')
            ->where('active', true)
            ->select('id', 'name', 'manager', 'department', 'active')
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Constrói mapa de usuários por nome para lookup rápido
     */
    private function buildUserNameMap(Collection $users): array
    {
        $nameMap = [];
        
        foreach ($users as $user) {
            // Indexar por nome completo
            $nameMap[strtolower($user->name)] = $user;
            
            // Indexar por partes do nome para matching flexível
            $nameParts = explode(' ', $user->name);
            foreach ($nameParts as $part) {
                if (strlen($part) > 2) { // Evitar partes muito pequenas
                    $key = strtolower($part);
                    if (!isset($nameMap[$key])) {
                        $nameMap[$key] = $user;
                    }
                }
            }
        }
        
        return $nameMap;
    }
    
    /**
     * Constrói estrutura da árvore usando algoritmo otimizado
     */
    private function buildTreeStructure(Collection $users, array $usersByName): array
    {
        $tree = [];
        $processed = [];
        
        foreach ($users as $user) {
            if (isset($processed[$user->id])) {
                continue;
            }
            
            $node = $this->buildUserNode($user, $users, $usersByName, $processed);
            if ($node) {
                $tree[$user->id] = $node;
            }
        }
        
        return $tree;
    }
    
    /**
     * Constrói nó da árvore para um usuário
     */
    private function buildUserNode(User $user, Collection $allUsers, array $usersByName, array &$processed): ?array
    {
        if (isset($processed[$user->id])) {
            return null;
        }
        
        $processed[$user->id] = true;
        
        $node = [
            'id' => $user->id,
            'name' => $user->name,
            'department' => $user->department,
            'roles' => $user->roles->pluck('name')->toArray(),
            'manager_id' => null,
            'manager_name' => null,
            'subordinates' => [],
            'level' => 0
        ];
        
        // Encontrar gestor
        if ($user->manager) {
            $manager = $this->findManagerFromDN($user->manager, $usersByName);
            if ($manager) {
                $node['manager_id'] = $manager->id;
                $node['manager_name'] = $manager->name;
            }
        }
        
        // Encontrar subordinados diretos
        $subordinates = $this->findDirectSubordinates($user, $allUsers, $usersByName);
        foreach ($subordinates as $subordinate) {
            $subordinateNode = $this->buildUserNode($subordinate, $allUsers, $usersByName, $processed);
            if ($subordinateNode) {
                $subordinateNode['level'] = $node['level'] + 1;
                $node['subordinates'][] = $subordinateNode;
            }
        }
        
        return $node;
    }
    
    /**
     * Encontra gestor a partir do DN do LDAP
     */
    private function findManagerFromDN(string $managerDN, array $usersByName): ?User
    {
        // Extrair nome do DN
        if (!preg_match('/CN=([^,]+)/', $managerDN, $matches)) {
            return null;
        }
        
        $managerName = trim($matches[1]);
        $key = strtolower($managerName);
        
        // Busca exata primeiro
        if (isset($usersByName[$key])) {
            return $usersByName[$key];
        }
        
        // Busca parcial
        foreach ($usersByName as $name => $user) {
            if (strpos($name, $key) !== false || strpos($key, $name) !== false) {
                return $user;
            }
        }
        
        return null;
    }
    
    /**
     * Encontra subordinados diretos de um usuário
     */
    private function findDirectSubordinates(User $manager, Collection $allUsers, array $usersByName): Collection
    {
        return $allUsers->filter(function ($user) use ($manager, $usersByName) {
            if (!$user->manager || $user->id === $manager->id) {
                return false;
            }
            
            $userManager = $this->findManagerFromDN($user->manager, $usersByName);
            return $userManager && $userManager->id === $manager->id;
        });
    }
    
    /**
     * Encontra subordinados recursivamente
     */
    private function findSubordinatesRecursive(User $manager, int $maxDepth, int $currentDepth = 0): array
    {
        if ($currentDepth >= $maxDepth) {
            return [];
        }
        
        $allUsers = $this->loadAllActiveUsers();
        $usersByName = $this->buildUserNameMap($allUsers);
        
        $directSubordinates = $this->findDirectSubordinates($manager, $allUsers, $usersByName);
        $result = [];
        
        foreach ($directSubordinates as $subordinate) {
            $subordinateData = [
                'id' => $subordinate->id,
                'name' => $subordinate->name,
                'department' => $subordinate->department,
                'level' => $currentDepth + 1,
                'subordinates' => $this->findSubordinatesRecursive($subordinate, $maxDepth, $currentDepth + 1)
            ];
            
            $result[] = $subordinateData;
        }
        
        return $result;
    }
    
    /**
     * Detecta ciclo a partir de um usuário
     */
    private function detectCycleFromUser(User $user, array &$visited, array &$recursionStack, Collection $allUsers): array
    {
        $visited[$user->id] = true;
        $recursionStack[$user->id] = true;
        
        if ($user->manager) {
            $usersByName = $this->buildUserNameMap($allUsers);
            $manager = $this->findManagerFromDN($user->manager, $usersByName);
            
            if ($manager) {
                if (!isset($visited[$manager->id])) {
                    $cycle = $this->detectCycleFromUser($manager, $visited, $recursionStack, $allUsers);
                    if (!empty($cycle)) {
                        return $cycle;
                    }
                } elseif (isset($recursionStack[$manager->id])) {
                    // Ciclo detectado
                    return [
                        'cycle_start' => $manager->id,
                        'cycle_end' => $user->id,
                        'users_involved' => [$user->id, $manager->id]
                    ];
                }
            }
        }
        
        unset($recursionStack[$user->id]);
        return [];
    }
    
    /**
     * Encontra usuários órfãos (com manager inexistente)
     */
    private function findOrphanUsers(): array
    {
        $users = $this->loadAllActiveUsers();
        $usersByName = $this->buildUserNameMap($users);
        $orphans = [];
        
        foreach ($users as $user) {
            if ($user->manager) {
                $manager = $this->findManagerFromDN($user->manager, $usersByName);
                if (!$manager) {
                    $orphans[] = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'manager_dn' => $user->manager
                    ];
                }
            }
        }
        
        return $orphans;
    }
    
    /**
     * Encontra nós com profundidade excessiva
     */
    private function findExcessivelyDeepNodes(array $tree): array
    {
        $deepNodes = [];
        
        foreach ($tree as $node) {
            $depth = $this->calculateNodeDepth($node);
            if ($depth > self::MAX_HIERARCHY_DEPTH) {
                $deepNodes[] = [
                    'user_id' => $node['id'],
                    'user_name' => $node['name'],
                    'depth' => $depth
                ];
            }
        }
        
        return $deepNodes;
    }
    
    /**
     * Calcula profundidade máxima da árvore
     */
    private function calculateMaxDepth(array $tree): int
    {
        $maxDepth = 0;
        
        foreach ($tree as $node) {
            $depth = $this->calculateNodeDepth($node);
            $maxDepth = max($maxDepth, $depth);
        }
        
        return $maxDepth;
    }
    
    /**
     * Calcula profundidade de um nó
     */
    private function calculateNodeDepth(array $node): int
    {
        $maxSubordinateDepth = 0;
        
        if (!empty($node['subordinates'])) {
            foreach ($node['subordinates'] as $subordinate) {
                $subordinateDepth = $this->calculateNodeDepth($subordinate);
                $maxSubordinateDepth = max($maxSubordinateDepth, $subordinateDepth);
            }
        }
        
        return 1 + $maxSubordinateDepth;
    }
}