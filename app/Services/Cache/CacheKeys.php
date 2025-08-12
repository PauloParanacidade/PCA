<?php

namespace App\Services\Cache;

/**
 * Constantes para chaves de cache da hierarquia
 * Centraliza todas as chaves para facilitar manutenção
 */
class CacheKeys
{
    // Cache da árvore hierárquica completa
    public const HIERARCHY_TREE = 'hierarchy:tree';
    
    // Cache de subordinados por usuário (formato: hierarchy:subordinates:123)
    public const USER_SUBORDINATES = 'hierarchy:subordinates:%d';
    
    // Cache de gestores por usuário (formato: hierarchy:managers:123)
    public const USER_MANAGERS = 'hierarchy:managers:%d';
    
    // Cache de permissões por usuário (formato: hierarchy:permissions:123)
    public const USER_PERMISSIONS = 'hierarchy:permissions:%d';
    
    // Cache de validação gestor-subordinado (formato: hierarchy:validation:123:456)
    public const MANAGER_VALIDATION = 'hierarchy:validation:%d:%d';
    
    // Versão do cache para invalidação inteligente
    public const HIERARCHY_VERSION = 'hierarchy:version';
    
    // Cache de usuários ativos com roles
    public const ACTIVE_USERS_WITH_ROLES = 'hierarchy:active_users_roles';
    
    // Cache de estrutura departamental
    public const DEPARTMENT_STRUCTURE = 'hierarchy:departments';
    
    // Cache de gestores especiais (DAF, Secretária, etc.)
    public const SPECIAL_MANAGERS = 'hierarchy:special_managers';
    
    // Métricas de performance do cache
    public const CACHE_METRICS = 'hierarchy:metrics';
    
    /**
     * Gera chave formatada para subordinados de um usuário
     */
    public static function userSubordinates(int $userId, int $version = null): string
    {
        $key = sprintf(self::USER_SUBORDINATES, $userId);
        return $version ? $key . ':' . $version : $key;
    }
    
    /**
     * Gera chave formatada para gestores de um usuário
     */
    public static function userManagers(int $userId, int $version = null): string
    {
        $key = sprintf(self::USER_MANAGERS, $userId);
        return $version ? $key . ':' . $version : $key;
    }
    
    /**
     * Gera chave formatada para permissões de um usuário
     */
    public static function userPermissions(int $userId, int $version = null): string
    {
        $key = sprintf(self::USER_PERMISSIONS, $userId);
        return $version ? $key . ':' . $version : $key;
    }
    
    /**
     * Gera chave formatada para validação gestor-subordinado
     */
    public static function managerValidation(int $managerId, int $subordinateId, int $version = null): string
    {
        $key = sprintf(self::MANAGER_VALIDATION, $managerId, $subordinateId);
        return $version ? $key . ':' . $version : $key;
    }
    
    /**
     * Gera chave versionada para qualquer cache
     */
    public static function withVersion(string $baseKey, int $version): string
    {
        return $baseKey . ':' . $version;
    }
    
    /**
     * Lista todas as chaves de cache para limpeza
     */
    public static function getAllKeys(): array
    {
        return [
            self::HIERARCHY_TREE,
            self::USER_SUBORDINATES,
            self::USER_MANAGERS,
            self::USER_PERMISSIONS,
            self::MANAGER_VALIDATION,
            self::HIERARCHY_VERSION,
            self::ACTIVE_USERS_WITH_ROLES,
            self::DEPARTMENT_STRUCTURE,
            self::SPECIAL_MANAGERS,
            self::CACHE_METRICS,
        ];
    }
}