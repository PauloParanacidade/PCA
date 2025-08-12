<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Hierarchy\HierarquiaCacheService;
use App\Services\Hierarchy\HierarchyTreeBuilder;
use App\Services\Hierarchy\HierarchyQueryOptimizer;
use Illuminate\Support\Facades\Event;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Service Provider para os serviços de hierarquia
 * 
 * Responsável por:
 * - Registrar serviços de hierarquia no container
 * - Configurar listeners para invalidação de cache
 * - Inicializar configurações de hierarquia
 * - Registrar comandos artisan relacionados
 */
class HierarchyServiceProvider extends ServiceProvider
{
    /**
     * Registrar serviços no container
     */
    public function register(): void
    {
        // Registrar HierarchyTreeBuilder como singleton
        $this->app->singleton(HierarchyTreeBuilder::class, function ($app) {
            return new HierarchyTreeBuilder();
        });
        
        // Registrar HierarchyQueryOptimizer como singleton
        $this->app->singleton(HierarchyQueryOptimizer::class, function ($app) {
            return new HierarchyQueryOptimizer();
        });
        
        // Registrar HierarquiaCacheService como singleton
        $this->app->singleton(HierarquiaCacheService::class, function ($app) {
            return new HierarquiaCacheService(
                $app->make(HierarchyTreeBuilder::class),
                $app->make(HierarchyQueryOptimizer::class)
            );
        });
        
        // Registrar alias para facilitar injeção de dependência
        $this->app->alias(HierarquiaCacheService::class, 'hierarchy.cache');
        $this->app->alias(HierarchyTreeBuilder::class, 'hierarchy.tree');
        $this->app->alias(HierarchyQueryOptimizer::class, 'hierarchy.optimizer');
        
        Log::info('🔧 Serviços de hierarquia registrados no container');
    }
    
    /**
     * Bootstrap dos serviços
     */
    public function boot(): void
    {
        // Publicar arquivo de configuração
        $this->publishes([
            __DIR__.'/../../config/hierarchy_cache.php' => config_path('hierarchy_cache.php'),
        ], 'hierarchy-config');
        
        // Registrar listeners para invalidação automática de cache
        $this->registerCacheInvalidationListeners();
        
        // Registrar comandos artisan
        $this->registerCommands();
        
        // Configurar cache warming automático se habilitado
        $this->configureCacheWarming();
        
        Log::info('✅ HierarchyServiceProvider inicializado com sucesso');
    }
    
    /**
     * Registra listeners para invalidação automática de cache
     */
    private function registerCacheInvalidationListeners(): void
    {
        $cacheService = $this->app->make(HierarquiaCacheService::class);
        
        // Listener para quando usuário é atualizado
        Event::listen('eloquent.updated: ' . User::class, function ($user) use ($cacheService) {
            if ($this->shouldInvalidateOnUserUpdate($user)) {
                Log::info('👤 Invalidando cache por atualização de usuário', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'changed_fields' => array_keys($user->getDirty())
                ]);
                
                $cacheService->invalidateUserCache($user->id);
                
                // Se manager ou department mudaram, invalidar cache completo
                if ($user->wasChanged(['manager', 'department', 'active'])) {
                    $cacheService->invalidateFullCache('user_hierarchy_change');
                }
            }
        });
        
        // Listener para quando usuário é criado
        Event::listen('eloquent.created: ' . User::class, function ($user) use ($cacheService) {
            Log::info('👤 Invalidando cache por criação de usuário', [
                'user_id' => $user->id,
                'user_name' => $user->name
            ]);
            
            $cacheService->invalidateFullCache('new_user_created');
        });
        
        // Listener para quando usuário é deletado
        Event::listen('eloquent.deleted: ' . User::class, function ($user) use ($cacheService) {
            Log::info('👤 Invalidando cache por remoção de usuário', [
                'user_id' => $user->id,
                'user_name' => $user->name
            ]);
            
            $cacheService->invalidateUserCache($user->id);
            $cacheService->invalidateFullCache('user_deleted');
        });
        
        Log::info('🔄 Listeners de invalidação de cache registrados');
    }
    
    /**
     * Verifica se deve invalidar cache na atualização do usuário
     */
    private function shouldInvalidateOnUserUpdate(User $user): bool
    {
        $config = config('hierarchy_cache.invalidation', []);
        
        // Verificar se invalidação está habilitada
        if (!($config['on_user_update'] ?? true)) {
            return false;
        }
        
        // Campos que afetam a hierarquia
        $hierarchyFields = ['manager', 'department', 'active'];
        
        // Verificar se algum campo relevante foi alterado
        foreach ($hierarchyFields as $field) {
            if ($user->wasChanged($field)) {
                $configKey = "on_{$field}_change";
                if ($config[$configKey] ?? true) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Registra comandos artisan relacionados à hierarquia
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\HierarchyClearCache::class,
                \App\Console\Commands\HierarchyWarmCache::class,
                \App\Console\Commands\HierarchyCacheMetrics::class,
            ]);
            
            Log::info('📋 Comandos Artisan de hierarquia registrados', [
                'commands' => [
                    'hierarchy:clear-cache',
                    'hierarchy:warm-cache', 
                    'hierarchy:cache-metrics'
                ]
            ]);
        }
    }
    
    /**
     * Configura cache warming automático
     */
    private function configureCacheWarming(): void
    {
        $config = config('hierarchy_cache.performance', []);
        
        if (!($config['auto_warm_cache'] ?? true)) {
            return;
        }
        
        // Agendar cache warming se estiver em produção
        if ($this->app->environment('production')) {
            $this->app->booted(function () {
                $cacheService = $this->app->make(HierarquiaCacheService::class);
                
                // Warm cache em background após 30 segundos
                $this->app->make('queue')->later(30, function () use ($cacheService) {
                    try {
                        $cacheService->warmCache();
                        Log::info('🔥 Cache warming automático executado com sucesso');
                    } catch (\Exception $e) {
                        Log::error('❌ Erro no cache warming automático', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                });
            });
        }
    }
    
    /**
     * Serviços fornecidos por este provider
     */
    public function provides(): array
    {
        return [
            HierarquiaCacheService::class,
            HierarchyTreeBuilder::class,
            HierarchyQueryOptimizer::class,
            'hierarchy.cache',
            'hierarchy.tree',
            'hierarchy.optimizer',
        ];
    }
}