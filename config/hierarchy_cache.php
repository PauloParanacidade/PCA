<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache da Hierarquia - Configurações
    |--------------------------------------------------------------------------
    |
    | Configurações para o sistema de cache da hierarquia organizacional.
    | Essas configurações controlam TTL, estratégias de invalidação,
    | e otimizações de performance.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | TTL (Time To Live) do Cache
    |--------------------------------------------------------------------------
    |
    | Define por quanto tempo os dados ficam em cache antes de expirarem.
    | Valores em segundos.
    |
    */
    'ttl' => [
        // Cache da árvore hierárquica completa (30 minutos)
        'hierarchy_tree' => env('HIERARCHY_CACHE_TREE_TTL', 1800),
        
        // Cache de subordinados por usuário (15 minutos)
        'user_subordinates' => env('HIERARCHY_CACHE_SUBORDINATES_TTL', 900),
        
        // Cache de gestores por usuário (15 minutos)
        'user_managers' => env('HIERARCHY_CACHE_MANAGERS_TTL', 900),
        
        // Cache de permissões hierárquicas (10 minutos)
        'user_permissions' => env('HIERARCHY_CACHE_PERMISSIONS_TTL', 600),
        
        // Cache de validação de gestão (5 minutos)
        'manager_validation' => env('HIERARCHY_CACHE_VALIDATION_TTL', 300),
        
        // Cache de usuários ativos com roles (20 minutos)
        'active_users_roles' => env('HIERARCHY_CACHE_USERS_ROLES_TTL', 1200),
        
        // Cache de estrutura departamental (1 hora)
        'department_structure' => env('HIERARCHY_CACHE_DEPARTMENT_TTL', 3600),
        
        // Cache de gestores especiais (1 hora)
        'special_managers' => env('HIERARCHY_CACHE_SPECIAL_TTL', 3600),
        
        // Cache de métricas (5 minutos)
        'metrics' => env('HIERARCHY_CACHE_METRICS_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Estratégias de Invalidação
    |--------------------------------------------------------------------------
    |
    | Define quando e como o cache deve ser invalidado.
    |
    */
    'invalidation' => [
        // Invalidar cache quando usuário é atualizado
        'on_user_update' => env('HIERARCHY_CACHE_INVALIDATE_USER_UPDATE', true),
        
        // Invalidar cache quando role é alterada
        'on_role_change' => env('HIERARCHY_CACHE_INVALIDATE_ROLE_CHANGE', true),
        
        // Invalidar cache quando manager é alterado
        'on_manager_change' => env('HIERARCHY_CACHE_INVALIDATE_MANAGER_CHANGE', true),
        
        // Invalidar cache quando department é alterado
        'on_department_change' => env('HIERARCHY_CACHE_INVALIDATE_DEPARTMENT_CHANGE', true),
        
        // Invalidar cache quando usuário é desativado
        'on_user_deactivation' => env('HIERARCHY_CACHE_INVALIDATE_USER_DEACTIVATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Performance
    |--------------------------------------------------------------------------
    |
    | Configurações para otimizar a performance do sistema de cache.
    |
    */
    'performance' => [
        // Tamanho do batch para operações em lote
        'batch_size' => env('HIERARCHY_CACHE_BATCH_SIZE', 100),
        
        // Profundidade máxima da hierarquia
        'max_hierarchy_depth' => env('HIERARCHY_MAX_DEPTH', 10),
        
        // Níveis máximos para busca de subordinados
        'max_subordinate_levels' => env('HIERARCHY_MAX_SUBORDINATE_LEVELS', 3),
        
        // Níveis máximos para busca de gestores
        'max_manager_levels' => env('HIERARCHY_MAX_MANAGER_LEVELS', 5),
        
        // Timeout para operações de cache (segundos)
        'cache_timeout' => env('HIERARCHY_CACHE_TIMEOUT', 30),
        
        // Habilitar cache warming automático
        'auto_warm_cache' => env('HIERARCHY_AUTO_WARM_CACHE', true),
        
        // Intervalo para cache warming (minutos)
        'warm_cache_interval' => env('HIERARCHY_WARM_CACHE_INTERVAL', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Monitoramento
    |--------------------------------------------------------------------------
    |
    | Configurações para monitorar a performance e uso do cache.
    |
    */
    'monitoring' => [
        // Habilitar coleta de métricas
        'enable_metrics' => env('HIERARCHY_ENABLE_METRICS', true),
        
        // Habilitar logs detalhados
        'enable_detailed_logs' => env('HIERARCHY_ENABLE_DETAILED_LOGS', false),
        
        // Threshold para logs de performance (segundos)
        'slow_query_threshold' => env('HIERARCHY_SLOW_QUERY_THRESHOLD', 1.0),
        
        // Habilitar alertas para cache miss alto
        'enable_cache_miss_alerts' => env('HIERARCHY_ENABLE_CACHE_MISS_ALERTS', true),
        
        // Threshold para alerta de cache miss (%)
        'cache_miss_alert_threshold' => env('HIERARCHY_CACHE_MISS_ALERT_THRESHOLD', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Segurança
    |--------------------------------------------------------------------------
    |
    | Configurações relacionadas à segurança do cache hierárquico.
    |
    */
    'security' => [
        // Criptografar dados sensíveis no cache
        'encrypt_sensitive_data' => env('HIERARCHY_ENCRYPT_CACHE', false),
        
        // Validar integridade dos dados em cache
        'validate_cache_integrity' => env('HIERARCHY_VALIDATE_CACHE_INTEGRITY', true),
        
        // Limpar cache automaticamente em caso de inconsistência
        'auto_clear_on_inconsistency' => env('HIERARCHY_AUTO_CLEAR_INCONSISTENT', true),
        
        // Roles que podem limpar cache manualmente
        'cache_clear_roles' => ['admin', 'daf'],
        
        // Auditoria de operações de cache
        'enable_cache_audit' => env('HIERARCHY_ENABLE_CACHE_AUDIT', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Fallback
    |--------------------------------------------------------------------------
    |
    | Configurações para quando o cache não está disponível.
    |
    */
    'fallback' => [
        // Usar consultas diretas quando cache falha
        'use_direct_queries_on_failure' => env('HIERARCHY_USE_DIRECT_QUERIES_FALLBACK', true),
        
        // Timeout para fallback (segundos)
        'fallback_timeout' => env('HIERARCHY_FALLBACK_TIMEOUT', 10),
        
        // Tentar recriar cache automaticamente após falha
        'auto_rebuild_on_failure' => env('HIERARCHY_AUTO_REBUILD_ON_FAILURE', true),
        
        // Intervalo entre tentativas de rebuild (minutos)
        'rebuild_retry_interval' => env('HIERARCHY_REBUILD_RETRY_INTERVAL', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Desenvolvimento
    |--------------------------------------------------------------------------
    |
    | Configurações específicas para ambiente de desenvolvimento.
    |
    */
    'development' => [
        // Desabilitar cache em desenvolvimento
        'disable_cache_in_dev' => env('HIERARCHY_DISABLE_CACHE_DEV', false),
        
        // Mostrar debug info nas respostas
        'show_debug_info' => env('HIERARCHY_SHOW_DEBUG_INFO', false),
        
        // Simular latência de rede para testes
        'simulate_network_latency' => env('HIERARCHY_SIMULATE_LATENCY', false),
        
        // Latência simulada (milissegundos)
        'simulated_latency_ms' => env('HIERARCHY_SIMULATED_LATENCY_MS', 100),
    ],
];