<?php

namespace App\Services\Hierarchy;

/**
 * Classe utilitária para gerenciar chaves de cache da hierarquia
 * 
 * Centraliza todas as chaves de cache utilizadas pelo sistema de hierarquia,
 * garantindo consistência e facilitando manutenção.
 */
class CacheKeys
{
    // Chaves base do cache
    const HIERARCHY_TREE = 'hierarchy:tree';
    const ACTIVE_USERS_WITH_ROLES = 'hierarchy:active_users_roles';
    const SPECIAL_MANAGERS = 'hierarchy:special_managers';
    const HIERARCHY_VERSION = 'hierarchy:version';
    const CACHE_METRICS = 'hierarchy:cache_metrics';
    
    // Prefixos para chaves dinâmicas
    const USER_SUBORDINATES_PREFIX = 'hierarchy:user_subordinates';
    const USER_MANAGERS_PREFIX = 'hierarchy:user_managers';
    const USER_PERMISSIONS_PREFIX = 'hierarchy:user_permissions';
    const MANAGER_VALIDATION_PREFIX = 'hierarchy:manager_validation';
    
    /**
     * Gera chave para subordinados de um usuário
     */
    public static function userSubordinates(int $userId, int $version = null): string
    {
        $key = self::USER_SUBORDINATES_PREFIX . ':' . $userId;
        return $version ? self::withVersion($key, $version) : $key;
    }
    
    /**
     * Gera chave para gerentes de um usuário
     */
    public static function userManagers(int $userId, int $version = null): string
    {
        $key = self::USER_MANAGERS_PREFIX . ':' . $userId;
        return $version ? self::withVersion($key, $version) : $key;
    }
    
    /**
     * Gera chave para permissões de um usuário
     */
    public static function userPermissions(int $userId, int $version = null): string
    {
        $key = self::USER_PERMISSIONS_PREFIX . ':' . $userId;
        return $version ? self::withVersion($key, $version) : $key;
    }
    
    /**
     * Gera chave para validação de gerente
     */
    public static function managerValidation(int $managerId, int $subordinateId, int $version = null): string
    {
        $key = self::MANAGER_VALIDATION_PREFIX . ':' . $managerId . ':' . $subordinateId;
        return $version ? self::withVersion($key, $version) : $key;
    }
    
    /**
     * Adiciona versão à chave de cache
     */
    public static function withVersion(string $key, int $version): string
    {
        return $key . ':v' . $version;
    }
    
    /**
     * Retorna todas as chaves base do sistema
     */
    public static function getAllKeys(): array
    {
        return [
            self::HIERARCHY_TREE,
            self::ACTIVE_USERS_WITH_ROLES,
            self::SPECIAL_MANAGERS,
            self::USER_SUBORDINATES_PREFIX,
            self::USER_MANAGERS_PREFIX,
            self::USER_PERMISSIONS_PREFIX,
            self::MANAGER_VALIDATION_PREFIX,
        ];
    }
    
    /**
     * Gera padrão para buscar chaves relacionadas a um usuário
     */
    public static function getUserRelatedPattern(int $userId): array
    {
        return [
            self::USER_SUBORDINATES_PREFIX . ':' . $userId . '*',
            self::USER_MANAGERS_PREFIX . ':' . $userId . '*',
            self::USER_PERMISSIONS_PREFIX . ':' . $userId . '*',
            self::MANAGER_VALIDATION_PREFIX . ':' . $userId . ':*',
            self::MANAGER_VALIDATION_PREFIX . ':*:' . $userId,
        ];
    }
    
    /**
     * Gera chave para cache de departamento
     */
    public static function departmentUsers(string $department, int $version = null): string
    {
        $key = 'hierarchy:department:' . md5($department);
        return $version ? self::withVersion($key, $version) : $key;
    }
    
    /**
     * Gera chave para cache de estatísticas
     */
    public static function hierarchyStats(int $version = null): string
    {
        $key = 'hierarchy:stats';
        return $version ? self::withVersion($key, $version) : $key;
    }
    
    /**
     * Gera chave para cache de validação de integridade
     */
    public static function integrityValidation(int $version = null): string
    {
        $key = 'hierarchy:integrity_validation';
        return $version ? self::withVersion($key, $version) : $key;
    }
    
    /**
     * Gera chave para cache de profundidade máxima
     */
    public static function maxDepth(int $version = null): string
    {
        $key = 'hierarchy:max_depth';
        return $version ? self::withVersion($key, $version) : $key;
    }
    
    /**
     * Gera chave para cache de contagem de gerentes
     */
    public static function managersCount(int $version = null): string
    {
        $key = 'hierarchy:managers_count';
        return $version ? self::withVersion($key, $version) : $key;
    }
    
    /**
     * Gera chave para cache temporário de aquecimento
     */
    public static function warmingProgress(string $type): string
    {
        return 'hierarchy:warming:' . $type . ':progress';
    }
    
    /**
     * Gera chave para lock de operações críticas
     */
    public static function operationLock(string $operation): string
    {
        return 'hierarchy:lock:' . $operation;
    }
}