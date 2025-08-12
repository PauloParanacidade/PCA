<?php

namespace App\Services\Hierarchy;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * Otimizador de consultas hierárquicas
 * 
 * Responsável por:
 * - Otimizar consultas de hierarquia para reduzir N+1
 * - Implementar estratégias de busca eficientes
 * - Cachear resultados de consultas frequentes
 * - Monitorar performance das consultas
 */
class HierarchyQueryOptimizer
{
    private const QUERY_CACHE_TTL = 300; // 5 minutos
    private const BATCH_SIZE = 100;
    
    /**
     * Busca usuários com seus gestores de forma otimizada
     */
    public function getUsersWithManagers(array $userIds = null): Collection
    {
        $startTime = microtime(true);
        
        $query = User::with('roles:id,name')
            ->where('active', true)
            ->select('id', 'name', 'manager', 'department', 'active');
            
        if ($userIds) {
            $query->whereIn('id', $userIds);
        }
        
        $users = $query->get();
        
        // Resolver gestores em batch para evitar N+1
        $this->resolveManagersInBatch($users);
        
        $duration = microtime(true) - $startTime;
        
        Log::info('📊 Consulta otimizada de usuários com gestores', [
            'users_count' => $users->count(),
            'duration' => round($duration, 3) . 's',
            'with_filter' => !empty($userIds)
        ]);
        
        return $users;
    }
    
    /**
     * Busca subordinados de um usuário de forma otimizada
     */
    public function getSubordinatesOptimized(int $userId, int $maxDepth = 2): Collection
    {
        $startTime = microtime(true);
        
        $user = User::find($userId);
        if (!$user) {
            return collect();
        }
        
        // Usar CTE recursiva quando disponível (MySQL 8.0+)
        if ($this->supportsCTE()) {
            $subordinates = $this->getSubordinatesWithCTE($user, $maxDepth);
        } else {
            $subordinates = $this->getSubordinatesIterative($user, $maxDepth);
        }
        
        $duration = microtime(true) - $startTime;
        
        Log::info('👥 Consulta otimizada de subordinados', [
            'user_id' => $userId,
            'subordinates_count' => $subordinates->count(),
            'max_depth' => $maxDepth,
            'duration' => round($duration, 3) . 's',
            'method' => $this->supportsCTE() ? 'CTE' : 'Iterative'
        ]);
        
        return $subordinates;
    }
    
    /**
     * Busca gestores de um usuário até determinado nível
     */
    public function getManagersChain(int $userId, int $maxLevels = 3): Collection
    {
        $startTime = microtime(true);
        
        $user = User::find($userId);
        if (!$user) {
            return collect();
        }
        
        $managers = collect();
        $currentUser = $user;
        $level = 0;
        
        // Carregar todos os usuários uma vez para evitar múltiplas consultas
        $allUsers = $this->getUsersWithManagers();
        $usersByName = $this->buildUserNameMap($allUsers);
        
        while ($currentUser && $currentUser->manager && $level < $maxLevels) {
            $manager = $this->findManagerFromDN($currentUser->manager, $usersByName);
            
            if (!$manager || $managers->contains('id', $manager->id)) {
                // Evitar ciclos
                break;
            }
            
            $managers->push([
                'id' => $manager->id,
                'name' => $manager->name,
                'department' => $manager->department,
                'level' => $level + 1
            ]);
            
            $currentUser = $manager;
            $level++;
        }
        
        $duration = microtime(true) - $startTime;
        
        Log::info('⬆️ Consulta de cadeia de gestores', [
            'user_id' => $userId,
            'managers_count' => $managers->count(),
            'max_levels' => $maxLevels,
            'duration' => round($duration, 3) . 's'
        ]);
        
        return $managers;
    }
    
    /**
     * Verifica se usuário é gestor de outro de forma otimizada
     */
    public function isManagerOfOptimized(int $managerId, int $subordinateId, int $maxDepth = 2): bool
    {
        $startTime = microtime(true);
        
        if ($managerId === $subordinateId) {
            return false;
        }
        
        // Buscar cadeia de gestores do subordinado
        $managersChain = $this->getManagersChain($subordinateId, $maxDepth);
        $isManager = $managersChain->contains('id', $managerId);
        
        $duration = microtime(true) - $startTime;
        
        Log::debug('🔍 Verificação otimizada de gestão', [
            'manager_id' => $managerId,
            'subordinate_id' => $subordinateId,
            'is_manager' => $isManager,
            'duration' => round($duration, 3) . 's'
        ]);
        
        return $isManager;
    }
    
    /**
     * Busca usuários por departamento com hierarquia
     */
    public function getUsersByDepartmentWithHierarchy(string $department): Collection
    {
        $startTime = microtime(true);
        
        $users = User::with('roles:id,name')
            ->where('active', true)
            ->where('department', 'LIKE', "%{$department}%")
            ->select('id', 'name', 'manager', 'department', 'active')
            ->orderBy('name')
            ->get();
            
        // Resolver hierarquia para cada usuário
        $this->resolveManagersInBatch($users);
        
        $duration = microtime(true) - $startTime;
        
        Log::info('🏢 Consulta de usuários por departamento com hierarquia', [
            'department' => $department,
            'users_count' => $users->count(),
            'duration' => round($duration, 3) . 's'
        ]);
        
        return $users;
    }
    
    /**
     * Busca usuários ativos com roles específicas
     */
    public function getActiveUsersWithRoles(array $roleNames = []): Collection
    {
        $startTime = microtime(true);
        
        $query = User::with('roles:id,name')
            ->where('active', true)
            ->select('id', 'name', 'manager', 'department', 'active');
            
        if (!empty($roleNames)) {
            $query->whereHas('roles', function ($q) use ($roleNames) {
                $q->whereIn('name', $roleNames);
            });
        }
        
        $users = $query->orderBy('name')->get();
        
        $duration = microtime(true) - $startTime;
        
        Log::info('👤 Consulta de usuários ativos com roles', [
            'roles_filter' => $roleNames,
            'users_count' => $users->count(),
            'duration' => round($duration, 3) . 's'
        ]);
        
        return $users;
    }
    
    /**
     * Resolve gestores em batch para evitar N+1
     */
    private function resolveManagersInBatch(Collection $users): void
    {
        $usersByName = $this->buildUserNameMap($users);
        
        foreach ($users as $user) {
            if ($user->manager) {
                $manager = $this->findManagerFromDN($user->manager, $usersByName);
                $user->resolved_manager = $manager;
            }
        }
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
                if (strlen($part) > 2) {
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
     * Verifica se o banco suporta CTE (Common Table Expressions)
     */
    private function supportsCTE(): bool
    {
        try {
            $version = DB::select('SELECT VERSION() as version')[0]->version;
            
            // MySQL 8.0+ suporta CTE
            if (strpos($version, 'MySQL') !== false) {
                preg_match('/([0-9]+)\.([0-9]+)/', $version, $matches);
                if (isset($matches[1], $matches[2])) {
                    $major = (int) $matches[1];
                    $minor = (int) $matches[2];
                    return $major > 8 || ($major === 8 && $minor >= 0);
                }
            }
            
            return false;
        } catch (\Exception $e) {
            Log::warning('⚠️ Erro ao verificar suporte a CTE', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Busca subordinados usando CTE recursiva (MySQL 8.0+)
     */
    private function getSubordinatesWithCTE(User $manager, int $maxDepth): Collection
    {
        $sql = "
            WITH RECURSIVE subordinates AS (
                -- Caso base: subordinados diretos
                SELECT 
                    u.id,
                    u.name,
                    u.manager,
                    u.department,
                    1 as level
                FROM users u
                WHERE u.active = 1
                  AND u.manager LIKE CONCAT('%CN=', ?, '%')
                
                UNION ALL
                
                -- Caso recursivo: subordinados dos subordinados
                SELECT 
                    u.id,
                    u.name,
                    u.manager,
                    u.department,
                    s.level + 1
                FROM users u
                INNER JOIN subordinates s ON u.manager LIKE CONCAT('%CN=', s.name, '%')
                WHERE u.active = 1
                  AND s.level < ?
            )
            SELECT * FROM subordinates
            ORDER BY level, name
        ";
        
        $results = DB::select($sql, [$manager->name, $maxDepth]);
        
        return collect($results)->map(function ($row) {
            return (object) [
                'id' => $row->id,
                'name' => $row->name,
                'manager' => $row->manager,
                'department' => $row->department,
                'level' => $row->level
            ];
        });
    }
    
    /**
     * Busca subordinados de forma iterativa (fallback)
     */
    private function getSubordinatesIterative(User $manager, int $maxDepth): Collection
    {
        $allUsers = $this->getUsersWithManagers();
        $usersByName = $this->buildUserNameMap($allUsers);
        
        $subordinates = collect();
        $currentLevel = collect([$manager]);
        $level = 0;
        
        while ($currentLevel->isNotEmpty() && $level < $maxDepth) {
            $nextLevel = collect();
            
            foreach ($currentLevel as $currentManager) {
                $directSubordinates = $allUsers->filter(function ($user) use ($currentManager, $usersByName) {
                    if (!$user->manager || $user->id === $currentManager->id) {
                        return false;
                    }
                    
                    $userManager = $this->findManagerFromDN($user->manager, $usersByName);
                    return $userManager && $userManager->id === $currentManager->id;
                });
                
                foreach ($directSubordinates as $subordinate) {
                    if (!$subordinates->contains('id', $subordinate->id)) {
                        $subordinate->level = $level + 1;
                        $subordinates->push($subordinate);
                        $nextLevel->push($subordinate);
                    }
                }
            }
            
            $currentLevel = $nextLevel;
            $level++;
        }
        
        return $subordinates;
    }
    
    /**
     * Obtém estatísticas de performance das consultas
     */
    public function getQueryPerformanceStats(): array
    {
        // Implementar coleta de métricas de performance
        return [
            'total_queries' => 0,
            'avg_duration' => 0,
            'cache_hit_rate' => 0,
            'slow_queries' => []
        ];
    }
}