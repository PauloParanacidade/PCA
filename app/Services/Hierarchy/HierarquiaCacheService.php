<?php

namespace App\Services\Hierarchy;

use App\Models\User;
use App\Services\Hierarchy\HierarchyTreeBuilder;
use App\Services\Hierarchy\HierarchyQueryOptimizer;
use App\Services\Hierarchy\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Serviço de cache para otimização da hierarquia organizacional
 * 
 * Responsável por:
 * - Gerenciar cache da árvore hierárquica
 * - Implementar versionamento para invalidação inteligente
 * - Fornecer fallback para consultas diretas
 * - Coletar métricas de performance
 */
class HierarquiaCacheService
{
    private const CACHE_TTL = 3600; // 1 hora
    private const METRICS_TTL = 86400; // 24 horas
    private const MAX_CACHE_SIZE = 1000; // Máximo de entradas por tipo
    
    private HierarchyTreeBuilder $treeBuilder;
    private HierarchyQueryOptimizer $queryOptimizer;
    
    public function __construct(
        HierarchyTreeBuilder $treeBuilder,
        HierarchyQueryOptimizer $queryOptimizer
    ) {
        $this->treeBuilder = $treeBuilder;
        $this->queryOptimizer = $queryOptimizer;
    }
    
    /**
     * Obtém a árvore hierárquica completa do cache
     */
    public function getHierarchyTree(): array
    {
        $startTime = microtime(true);
        $version = $this->getCurrentVersion();
        $key = CacheKeys::withVersion(CacheKeys::HIERARCHY_TREE, $version);
        
        $tree = Cache::remember($key, self::CACHE_TTL, function () {
            Log::info('🌳 Construindo árvore hierárquica completa');
            return $this->treeBuilder->buildCompleteTree();
        });
        
        $this->recordMetric('hierarchy_tree', microtime(true) - $startTime, !empty($tree));
        
        return $tree;
    }
    
    /**
     * Obtém subordinados de um usuário do cache
     */
    public function getUserSubordinates(int $userId): array
    {
        $startTime = microtime(true);
        $version = $this->getCurrentVersion();
        $key = CacheKeys::userSubordinates($userId, $version);
        
        $subordinates = Cache::remember($key, self::CACHE_TTL, function () use ($userId) {
            Log::info('👥 Construindo lista de subordinados', ['user_id' => $userId]);
            return $this->queryOptimizer->getSubordinatesOptimized($userId)->toArray();
        });
        
        // Garantir que sempre retornamos um array
        if ($subordinates instanceof \Illuminate\Support\Collection) {
            $subordinates = $subordinates->toArray();
        }
        
        $this->recordMetric('user_subordinates', microtime(true) - $startTime, !empty($subordinates));
        
        return $subordinates;
    }
    
    /**
     * Obtém cadeia de gestores de um usuário do cache
     */
    public function getUserManagers(int $userId): array
    {
        $startTime = microtime(true);
        $version = $this->getCurrentVersion();
        $key = CacheKeys::userManagers($userId, $version);
        
        $managers = Cache::remember($key, self::CACHE_TTL, function () use ($userId) {
            Log::info('👔 Construindo cadeia de gestores', ['user_id' => $userId]);
            $user = User::find($userId);
            return $user ? $this->queryOptimizer->getManagersChain($userId)->toArray() : [];
        });
        
        // Garantir que sempre retornamos um array
        if ($managers instanceof \Illuminate\Support\Collection) {
            $managers = $managers->toArray();
        }
        
        $this->recordMetric('user_managers', microtime(true) - $startTime, !empty($managers));
        
        return $managers;
    }
    
    /**
     * Verifica se um usuário é gestor de outro (com cache)
     */
    public function isManagerOf(int $managerId, int $subordinateId): bool
    {
        $startTime = microtime(true);
        $version = $this->getCurrentVersion();
        $key = CacheKeys::managerValidation($managerId, $subordinateId, $version);
        
        $isManager = Cache::remember($key, self::CACHE_TTL, function () use ($managerId, $subordinateId) {
            Log::info('🔍 Validando relação gestor-subordinado', [
                'manager_id' => $managerId,
                'subordinate_id' => $subordinateId
            ]);
            
            return $this->queryOptimizer->validateManagerRelation($managerId, $subordinateId);
        });
        
        $this->recordMetric('manager_validation', microtime(true) - $startTime, true);
        
        return $isManager;
    }
    
    /**
     * Obtém usuários ativos com roles do cache
     */
    public function getActiveUsersWithRoles(): Collection
    {
        $startTime = microtime(true);
        $version = $this->getCurrentVersion();
        $key = CacheKeys::withVersion(CacheKeys::ACTIVE_USERS_WITH_ROLES, $version);
        
        $users = Cache::remember($key, self::CACHE_TTL, function () {
            Log::info('👤 Carregando usuários ativos com roles');
            return User::with('roles')
                ->where('active', true)
                ->select('id', 'name', 'manager', 'department', 'active')
                ->get();
        });
        
        $this->recordMetric('active_users', microtime(true) - $startTime, $users->isNotEmpty());
        
        return $users;
    }
    
    /**
     * Obtém gestores especiais (DAF, Secretária, etc.) do cache
     */
    public function getSpecialManagers(): array
    {
        $startTime = microtime(true);
        $version = $this->getCurrentVersion();
        $key = CacheKeys::withVersion(CacheKeys::SPECIAL_MANAGERS, $version);
        
        $managers = Cache::remember($key, self::CACHE_TTL, function () {
            Log::info('⭐ Carregando gestores especiais');
            
            return [
                'daf' => User::whereHas('roles', fn($q) => $q->whereIn('name', ['daf', 'admin']))
                    ->where('active', true)
                    ->where(fn($q) => $q->where('department', 'DAF')->orWhere('department', 'daf'))
                    ->first(),
                    
                'secretaria' => User::whereHas('roles', fn($q) => $q->where('name', 'secretaria'))
                    ->where('active', true)
                    ->first(),
                    
                'admin' => User::whereHas('roles', fn($q) => $q->where('name', 'admin'))
                    ->where('active', true)
                    ->first(),
            ];
        });
        
        $this->recordMetric('special_managers', microtime(true) - $startTime, !empty($managers));
        
        return $managers;
    }
    
    /**
     * Invalida todo o cache da hierarquia
     */
    public function invalidateHierarchyCache(): void
    {
        $oldVersion = $this->getCurrentVersion();
        $newVersion = $this->incrementVersion();
        
        Log::info('🔄 Cache de hierarquia invalidado', [
            'timestamp' => now(),
            'old_version' => $oldVersion,
            'new_version' => $newVersion,
            'reason' => 'Manual invalidation'
        ]);
        
        // Limpar métricas antigas
        $this->resetMetrics();
    }
    
    /**
     * Invalida cache específico de um usuário
     */
    public function invalidateUserCache(int $userId): void
    {
        $version = $this->getCurrentVersion();
        
        $keys = [
            CacheKeys::userSubordinates($userId, $version),
            CacheKeys::userManagers($userId, $version),
            CacheKeys::userPermissions($userId, $version),
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        Log::info('🗑️ Cache do usuário invalidado', [
            'user_id' => $userId,
            'keys_cleared' => count($keys)
        ]);
    }
    
    /**
     * Pré-aquece o cache para usuários críticos
     */
    public function warmupCache(array $userIds = []): void
    {
        $startTime = microtime(true);
        
        if (empty($userIds)) {
            // Pré-aquecer para gestores e usuários com roles especiais
            $userIds = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'daf', 'secretaria', 'gestor', 'dom', 'supex', 'doe']);
            })->where('active', true)->pluck('id')->toArray();
        }
        
        Log::info('🔥 Iniciando pré-aquecimento do cache', [
            'user_count' => count($userIds)
        ]);
        
        // Pré-aquecer árvore completa
        $this->getHierarchyTree();
        
        // Pré-aquecer gestores especiais
        $this->getSpecialManagers();
        
        // Pré-aquecer usuários ativos
        $this->getActiveUsersWithRoles();
        
        // Pré-aquecer dados específicos dos usuários
        foreach ($userIds as $userId) {
            $this->getUserSubordinates($userId);
            $this->getUserManagers($userId);
        }
        
        $duration = microtime(true) - $startTime;
        
        Log::info('✅ Pré-aquecimento do cache concluído', [
            'duration' => round($duration, 2) . 's',
            'users_processed' => count($userIds)
        ]);
    }
    
    /**
     * Obtém métricas de performance do cache
     */
    public function getCacheMetrics(): array
    {
        $metrics = Cache::get(CacheKeys::CACHE_METRICS, []);
        
        $hits = $metrics['cache_hits'] ?? 0;
        $misses = $metrics['cache_misses'] ?? 0;
        $totalRequests = $hits + $misses;
        $hitRate = $totalRequests > 0 ? ($hits / $totalRequests) * 100 : 0;
        
        return [
            'version' => $this->getCurrentVersion(),
            'last_invalidation' => $metrics['last_invalidation'] ?? null,
            'hits' => $hits,
            'misses' => $misses,
            'hit_rate' => round($hitRate, 2),
            'total_requests' => $totalRequests,
            'memory_usage' => $metrics['memory_usage'] ?? memory_get_usage(true),
            'average_response_time' => $this->calculateAverageResponseTime($metrics),
            'cache_hits' => $hits, // Manter compatibilidade
            'cache_misses' => $misses, // Manter compatibilidade
            'operations' => $metrics['operations'] ?? [],
        ];
    }
    
    /**
     * Limpa todo o cache da hierarquia (usar com cuidado)
     */
    public function clearAllCache(): void
    {
        $keys = CacheKeys::getAllKeys();
        $version = $this->getCurrentVersion();
        
        foreach ($keys as $baseKey) {
            // Limpar versões atuais e anteriores
            for ($v = max(1, $version - 2); $v <= $version + 1; $v++) {
                Cache::forget(CacheKeys::withVersion($baseKey, $v));
            }
        }
        
        // Reset da versão
        Cache::forget(CacheKeys::HIERARCHY_VERSION);
        
        Log::warning('🧹 Todo o cache da hierarquia foi limpo', [
            'timestamp' => now(),
            'keys_cleared' => count($keys)
        ]);
    }
    
    /**
     * Obtém a versão atual do cache
     */
    private function getCurrentVersion(): int
    {
        return Cache::get(CacheKeys::HIERARCHY_VERSION, 1);
    }
    
    /**
     * Incrementa a versão do cache
     */
    private function incrementVersion(): int
    {
        return Cache::increment(CacheKeys::HIERARCHY_VERSION) ?: 1;
    }
    
    /**
     * Registra métrica de operação
     */
    private function recordMetric(string $operation, float $duration, bool $hit): void
    {
        $metrics = Cache::get(CacheKeys::CACHE_METRICS, [
            'total_requests' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'operations' => []
        ]);
        
        $metrics['total_requests']++;
        
        if ($hit) {
            $metrics['cache_hits']++;
        } else {
            $metrics['cache_misses']++;
        }
        
        if (!isset($metrics['operations'][$operation])) {
            $metrics['operations'][$operation] = [
                'count' => 0,
                'total_duration' => 0,
                'avg_duration' => 0
            ];
        }
        
        $metrics['operations'][$operation]['count']++;
        $metrics['operations'][$operation]['total_duration'] += $duration;
        $metrics['operations'][$operation]['avg_duration'] = 
            $metrics['operations'][$operation]['total_duration'] / 
            $metrics['operations'][$operation]['count'];
        
        Cache::put(CacheKeys::CACHE_METRICS, $metrics, self::METRICS_TTL);
    }
    
    /**
     * Calcula taxa de acerto do cache
     */
    private function calculateHitRate(array $metrics): float
    {
        $total = ($metrics['cache_hits'] ?? 0) + ($metrics['cache_misses'] ?? 0);
        
        if ($total === 0) {
            return 0.0;
        }
        
        return round((($metrics['cache_hits'] ?? 0) / $total) * 100, 2);
    }
    
    /**
     * Calcula tempo médio de resposta
     */
    private function calculateAverageResponseTime(array $metrics): float
    {
        $operations = $metrics['operations'] ?? [];
        
        if (empty($operations)) {
            return 0.0;
        }
        
        $totalDuration = 0;
        $totalCount = 0;
        
        foreach ($operations as $operation) {
            $totalDuration += $operation['total_duration'];
            $totalCount += $operation['count'];
        }
        
        return $totalCount > 0 ? round($totalDuration / $totalCount * 1000, 2) : 0.0; // em ms
    }
    
    /**
     * Reseta as métricas
     */
    private function resetMetrics(): void
    {
        Cache::forget(CacheKeys::CACHE_METRICS);
    }
}