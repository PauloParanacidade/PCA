# Sistema de Hierarquia Organizacional

Este documento descreve o sistema completo de hierarquia organizacional implementado no projeto PCA.

## 📋 Visão Geral

O sistema de hierarquia organizacional foi desenvolvido para gerenciar eficientemente a estrutura hierárquica de usuários, otimizar consultas relacionadas à hierarquia e fornecer cache inteligente para melhor performance.

## 🏗️ Arquitetura

### Componentes Principais

1. **HierarchyTreeBuilder** - Constrói e valida a árvore hierárquica
2. **HierarchyQueryOptimizer** - Otimiza consultas hierárquicas
3. **HierarquiaCacheService** - Gerencia cache inteligente
4. **HierarchyServiceProvider** - Registra serviços e configurações
5. **Comandos Artisan** - Ferramentas de linha de comando

### Estrutura de Arquivos

```
app/
├── Services/
│   └── Hierarchy/
│       ├── HierarchyTreeBuilder.php
│       ├── HierarchyQueryOptimizer.php
│       └── HierarquiaCacheService.php
├── Providers/
│   └── HierarchyServiceProvider.php
└── Console/Commands/
    ├── HierarchyClearCache.php
    ├── HierarchyWarmCache.php
    └── HierarchyCacheMetrics.php

config/
└── hierarchy_cache.php

database/migrations/
└── xxxx_xx_xx_xxxxxx_add_hierarchy_indexes_to_users_table.php
```

## 🚀 Funcionalidades

### 1. Construção de Árvore Hierárquica

- **Árvore Completa**: Constrói toda a estrutura organizacional
- **Árvore de Subordinados**: Obtém subordinados de um usuário específico
- **Detecção de Ciclos**: Previne loops infinitos na hierarquia
- **Validação de Integridade**: Verifica consistência da estrutura

### 2. Otimização de Consultas

- **Batch Loading**: Carrega dados em lotes para reduzir N+1 queries
- **CTE (Common Table Expressions)**: Usa consultas recursivas otimizadas
- **Índices Estratégicos**: Índices otimizados para consultas hierárquicas
- **Cache de Resultados**: Cache inteligente de consultas frequentes

### 3. Sistema de Cache

- **Cache Hierárquico**: Cache em múltiplas camadas
- **Invalidação Inteligente**: Invalidação automática baseada em eventos
- **Métricas de Performance**: Monitoramento detalhado do cache
- **Aquecimento Automático**: Pre-carregamento de dados críticos

## 🛠️ Comandos Artisan

### Limpar Cache

```bash
# Limpar cache de usuário específico
php artisan hierarchy:clear-cache --user=123

# Limpar todo o cache
php artisan hierarchy:clear-cache --all

# Limpar cache e mostrar métricas
php artisan hierarchy:clear-cache --all --metrics
```

### Aquecer Cache

```bash
# Aquecimento essencial (padrão)
php artisan hierarchy:warm-cache

# Aquecimento completo
php artisan hierarchy:warm-cache --full

# Aquecer apenas gerentes
php artisan hierarchy:warm-cache --managers

# Aquecer por departamentos
php artisan hierarchy:warm-cache --departments

# Definir tamanho do lote
php artisan hierarchy:warm-cache --full --batch-size=50
```

### Métricas de Cache

```bash
# Mostrar métricas básicas
php artisan hierarchy:cache-metrics

# Mostrar métricas detalhadas
php artisan hierarchy:cache-metrics --detailed

# Monitorar em tempo real
php artisan hierarchy:cache-metrics --watch

# Monitorar com intervalo personalizado
php artisan hierarchy:cache-metrics --watch --interval=10

# Exportar métricas
php artisan hierarchy:cache-metrics --export=json --detailed
php artisan hierarchy:cache-metrics --export=csv
```

## 📊 Uso dos Serviços

### HierarchyTreeBuilder

```php
use App\Services\Hierarchy\HierarchyTreeBuilder;

// Injeção de dependência
public function __construct(HierarchyTreeBuilder $treeBuilder)
{
    $this->treeBuilder = $treeBuilder;
}

// Obter árvore completa
$tree = $this->treeBuilder->buildCompleteTree();

// Obter subordinados de um usuário
$subordinates = $this->treeBuilder->buildUserSubordinatesTree(123);

// Detectar ciclos
$hasCycles = $this->treeBuilder->detectCycles();

// Validar integridade
$validation = $this->treeBuilder->validateTreeIntegrity();
```

### HierarchyQueryOptimizer

```php
use App\Services\Hierarchy\HierarchyQueryOptimizer;

// Obter usuários com gerentes
$usersWithManagers = $this->queryOptimizer->getUsersWithManagers();

// Obter subordinados com otimização
$subordinates = $this->queryOptimizer->getUserSubordinates(123);

// Verificar se é gerente
$isManager = $this->queryOptimizer->isUserManager(123);

// Obter usuários por departamento
$deptUsers = $this->queryOptimizer->getUsersByDepartmentWithHierarchy('TI');
```

### HierarquiaCacheService

```php
use App\Services\Hierarchy\HierarquiaCacheService;

// Obter árvore hierárquica (com cache)
$tree = $this->cacheService->getHierarchicalTree();

// Obter subordinados (com cache)
$subordinates = $this->cacheService->getUserSubordinates(123);

// Obter gerentes (com cache)
$managers = $this->cacheService->getUserManagers(123);

// Invalidar cache de usuário
$this->cacheService->invalidateUserCache(123);

// Obter métricas
$metrics = $this->cacheService->getCacheMetrics();
```

## ⚙️ Configuração

### Arquivo de Configuração

O arquivo `config/hierarchy_cache.php` contém todas as configurações:

```php
return [
    'ttl' => [
        'hierarchy_tree' => 3600,        // 1 hora
        'user_subordinates' => 1800,     // 30 minutos
        'user_managers' => 1800,         // 30 minutos
        'department_users' => 900,       // 15 minutos
        'query_results' => 600,          // 10 minutos
    ],
    
    'invalidation' => [
        'auto_invalidate_on_user_update' => true,
        'cascade_invalidation' => true,
        'batch_invalidation' => true,
    ],
    
    'performance' => [
        'enable_query_optimization' => true,
        'batch_size' => 100,
        'max_depth' => 10,
        'auto_warm_cache' => true,
    ],
    
    // ... outras configurações
];
```

### Índices de Banco de Dados

Os seguintes índices foram criados para otimização:

```sql
-- Índice para manager
CREATE INDEX idx_users_manager ON users(manager);

-- Índice para department
CREATE INDEX idx_users_department ON users(department);

-- Índice composto para consultas ativas
CREATE INDEX idx_users_active_manager ON users(active, manager);
CREATE INDEX idx_users_active_department ON users(active, department);
CREATE INDEX idx_users_active_manager_department ON users(active, manager, department);
```

## 🔧 Monitoramento e Manutenção

### Métricas Importantes

- **Hit Rate**: Taxa de acerto do cache (ideal > 90%)
- **Memory Usage**: Uso de memória do cache
- **Response Time**: Tempo médio de resposta
- **Cache Efficiency**: Eficiência geral do cache

### Manutenção Recomendada

1. **Diária**: Verificar métricas de cache
2. **Semanal**: Executar aquecimento completo do cache
3. **Mensal**: Validar integridade da árvore hierárquica
4. **Conforme necessário**: Limpar cache após grandes mudanças

### Troubleshooting

#### Cache com baixa performance
```bash
# Verificar métricas
php artisan hierarchy:cache-metrics --detailed

# Limpar e reaquecer cache
php artisan hierarchy:clear-cache --all
php artisan hierarchy:warm-cache --full
```

#### Problemas de integridade
```php
// Verificar ciclos e problemas
$treeBuilder = app(HierarchyTreeBuilder::class);
$validation = $treeBuilder->validateTreeIntegrity();

if (!$validation['is_valid']) {
    // Tratar problemas encontrados
    foreach ($validation['issues'] as $issue) {
        Log::warning('Hierarchy issue: ' . $issue);
    }
}
```

## 🚀 Performance

### Benchmarks Esperados

- **Consulta de subordinados**: < 50ms (com cache)
- **Árvore completa**: < 200ms (com cache)
- **Hit rate do cache**: > 90%
- **Redução de queries**: 70-90% com otimizações

### Otimizações Implementadas

1. **Cache em múltiplas camadas**
2. **Índices estratégicos no banco**
3. **Batch loading para reduzir N+1**
4. **CTE para consultas recursivas**
5. **Invalidação inteligente de cache**
6. **Aquecimento automático**

## 📝 Logs

O sistema gera logs detalhados para monitoramento:

```
[2024-01-15 10:30:00] INFO: 🌳 Árvore hierárquica construída com sucesso
[2024-01-15 10:30:01] INFO: 📊 Cache aquecido: 150 usuários processados
[2024-01-15 10:30:02] INFO: 🔄 Cache invalidado para usuário ID: 123
[2024-01-15 10:30:03] WARNING: ⚠️ Ciclo detectado na hierarquia: usuário 456
```

## 🔒 Segurança

- **Validação de entrada**: Todos os parâmetros são validados
- **Prevenção de ciclos**: Sistema detecta e previne loops
- **Logs de auditoria**: Todas as operações são logadas
- **Rate limiting**: Proteção contra abuso de cache

## 🤝 Contribuição

Para contribuir com melhorias:

1. Seguir padrões de código Laravel
2. Adicionar testes unitários
3. Documentar mudanças
4. Testar performance
5. Atualizar este README

---

**Desenvolvido para o projeto PCA**  
*Sistema de hierarquia organizacional otimizado e escalável*