# 🏢 Sistema de Hierarquia Organizacional - PCA

## 📋 Visão Geral

O Sistema de Hierarquia Organizacional é uma solução completa para gerenciar estruturas hierárquicas em organizações, oferecendo cache otimizado, comandos Artisan e API RESTful.

## 🚀 Funcionalidades Principais

### ✅ Implementado
- ✅ **Cache Inteligente**: Sistema de cache em múltiplas camadas com Redis
- ✅ **API RESTful**: Endpoints completos para consulta e gerenciamento
- ✅ **Comandos Artisan**: Ferramentas de linha de comando para administração
- ✅ **Otimização de Consultas**: Redução de queries ao banco de dados
- ✅ **Validação de Integridade**: Verificação automática da consistência dos dados
- ✅ **Métricas e Monitoramento**: Acompanhamento de performance do cache
- ✅ **Documentação Completa**: Guias e exemplos de uso

## 🛠️ Instalação e Configuração

### 1. Configuração do Cache
```bash
# Publicar configuração (se necessário)
php artisan vendor:publish --tag=hierarchy-config

# Limpar cache existente
php artisan cache:clear

# Configurar Redis (recomendado)
# No .env:
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. Verificar Instalação
```bash
# Listar comandos disponíveis
php artisan list hierarchy

# Verificar rotas da API
php artisan route:list --path=api/hierarchy
```

## 📡 API Endpoints

### 🌐 Rotas Públicas (Sem Autenticação)

#### Estrutura Organizacional
```http
GET /api/public/hierarchy/structure
```
**Resposta:**
```json
{
  "status": "success",
  "message": "Estrutura organizacional obtida",
  "data": [
    {
      "id": 1,
      "name": "João Silva",
      "department": "TI",
      "level": 0,
      "has_subordinates": true,
      "subordinates_count": 3
    }
  ]
}
```

#### Estatísticas Gerais
```http
GET /api/public/hierarchy/stats
```
**Resposta:**
```json
{
  "status": "success",
  "message": "Estatísticas organizacionais",
  "data": {
    "total_users": 150,
    "total_managers": 25,
    "total_departments": 8,
    "max_hierarchy_depth": 4
  }
}
```

### 🔐 Rotas Autenticadas (Requer Token Sanctum)

#### Árvore Hierárquica Completa
```http
GET /api/hierarchy/tree
Authorization: Bearer {token}
```

#### Subordinados de um Usuário
```http
GET /api/hierarchy/users/{userId}/subordinates
Authorization: Bearer {token}
```

#### Gerentes de um Usuário
```http
GET /api/hierarchy/users/{userId}/managers
Authorization: Bearer {token}
```

#### Verificar se é Gerente
```http
GET /api/hierarchy/users/{userId}/is-manager
Authorization: Bearer {token}
```

#### Usuários por Departamento
```http
GET /api/hierarchy/departments/{department}/users
Authorization: Bearer {token}
```

#### Validar Integridade
```http
GET /api/hierarchy/validate
Authorization: Bearer {token}
```

#### Métricas do Cache
```http
GET /api/hierarchy/metrics
Authorization: Bearer {token}
```

#### Aquecer Cache
```http
POST /api/hierarchy/cache/warm
Authorization: Bearer {token}
Content-Type: application/json

{
  "type": "full", // ou "managers", "departments"
  "batch_size": 50
}
```

#### Limpar Cache de Usuário
```http
DELETE /api/hierarchy/users/{userId}/cache
Authorization: Bearer {token}
```

## 🖥️ Comandos Artisan

### 1. Limpar Cache
```bash
# Limpar cache de usuário específico
php artisan hierarchy:clear-cache --user=123

# Limpar todo o cache
php artisan hierarchy:clear-cache --all

# Mostrar métricas antes de limpar
php artisan hierarchy:clear-cache --all --show-metrics
```

### 2. Aquecer Cache
```bash
# Aquecimento completo
php artisan hierarchy:warm-cache --type=full

# Apenas gerentes
php artisan hierarchy:warm-cache --type=managers

# Por departamento
php artisan hierarchy:warm-cache --type=departments --department="TI"

# Com tamanho de lote personalizado
php artisan hierarchy:warm-cache --type=full --batch-size=100
```

### 3. Métricas do Cache
```bash
# Métricas básicas
php artisan hierarchy:cache-metrics

# Métricas detalhadas
php artisan hierarchy:cache-metrics --detailed

# Monitoramento em tempo real
php artisan hierarchy:cache-metrics --watch --interval=5

# Exportar para arquivo
php artisan hierarchy:cache-metrics --export=json > metrics.json
php artisan hierarchy:cache-metrics --export=csv > metrics.csv
```

## 💻 Exemplos de Uso em Código

### Controller Example
```php
<?php

namespace App\Http\Controllers;

use App\Services\Hierarchy\HierarquiaCacheService;
use Illuminate\Http\Request;

class ExampleController extends Controller
{
    protected $hierarchyCache;

    public function __construct(HierarquiaCacheService $hierarchyCache)
    {
        $this->hierarchyCache = $hierarchyCache;
    }

    public function getUserTeam(Request $request)
    {
        $userId = $request->user()->id;
        
        // Obter subordinados
        $subordinates = $this->hierarchyCache->getUserSubordinates($userId);
        
        // Obter gerentes
        $managers = $this->hierarchyCache->getUserManagers($userId);
        
        // Verificar se é gerente
        $isManager = $this->hierarchyCache->isUserManager($userId);
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'user_id' => $userId,
                'is_manager' => $isManager,
                'subordinates' => $subordinates,
                'managers' => $managers
            ]
        ]);
    }
}
```

### Service Usage
```php
<?php

namespace App\Services;

use App\Services\Hierarchy\HierarquiaCacheService;

class TeamService
{
    protected $hierarchyCache;

    public function __construct(HierarquiaCacheService $hierarchyCache)
    {
        $this->hierarchyCache = $hierarchyCache;
    }

    public function getTeamStructure($managerId)
    {
        // Obter árvore hierárquica
        $tree = $this->hierarchyCache->getHierarchicalTree();
        
        // Filtrar apenas a equipe do gerente
        $teamTree = $this->filterTreeByManager($tree, $managerId);
        
        return $teamTree;
    }

    public function calculateTeamSize($managerId)
    {
        $subordinates = $this->hierarchyCache->getUserSubordinates($managerId);
        return count($subordinates);
    }
}
```

## 🧪 Testes

### Teste Manual via Browser
1. Acesse: `http://localhost:8000/test-hierarchy-api.html`
2. Teste as rotas públicas clicando nos botões
3. Para rotas autenticadas, use um cliente REST como Postman

### Teste via cURL
```bash
# Testar rota pública
curl -X GET "http://localhost:8000/api/public/hierarchy/stats" \
     -H "Accept: application/json"

# Testar rota autenticada (substitua {token})
curl -X GET "http://localhost:8000/api/hierarchy/tree" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer {token}"
```

## 📊 Monitoramento e Performance

### Métricas Importantes
- **Hit Rate**: Taxa de acerto do cache (ideal > 80%)
- **Memory Usage**: Uso de memória do cache
- **Query Reduction**: Redução de consultas ao banco
- **Response Time**: Tempo de resposta das consultas

### Alertas Recomendados
- Hit rate < 70%
- Uso de memória > 80%
- Tempo de resposta > 500ms
- Falhas de validação de integridade

## 🔧 Troubleshooting

### Problemas Comuns

#### Cache não funciona
```bash
# Verificar configuração do Redis
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');

# Limpar e recriar cache
php artisan hierarchy:clear-cache --all
php artisan hierarchy:warm-cache --type=full
```

#### Rotas não encontradas
```bash
# Limpar cache de rotas
php artisan route:clear
php artisan route:cache

# Verificar se as rotas estão registradas
php artisan route:list --path=api/hierarchy
```

#### Performance baixa
```bash
# Verificar métricas
php artisan hierarchy:cache-metrics --detailed

# Otimizar cache
php artisan hierarchy:warm-cache --type=full --batch-size=100
```

## 📚 Documentação Adicional

- **Arquitetura Completa**: `HIERARCHY_SYSTEM.md`
- **Código dos Services**: `app/Services/Hierarchy/`
- **Comandos Artisan**: `app/Console/Commands/Hierarchy*`
- **Controller de Exemplo**: `app/Http/Controllers/HierarchyController.php`
- **Rotas da API**: `routes/api.php`

## 🤝 Contribuição

Para contribuir com o sistema:
1. Siga os padrões de código Laravel
2. Mantenha a cobertura de testes
3. Documente novas funcionalidades
4. Teste performance com dados reais

## 📞 Suporte

Para dúvidas ou problemas:
1. Consulte esta documentação
2. Verifique os logs: `storage/logs/laravel.log`
3. Execute diagnósticos: `php artisan hierarchy:cache-metrics --detailed`

---

**Versão**: 1.0.0  
**Última Atualização**: $(date +'%Y-%m-%d')  
**Compatibilidade**: Laravel 10.x, PHP 8.1+