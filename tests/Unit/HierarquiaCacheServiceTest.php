<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Hierarchy\HierarquiaCacheService;
use App\Services\Hierarchy\HierarchyTreeBuilder;
use App\Services\Hierarchy\HierarchyQueryOptimizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;

class HierarquiaCacheServiceTest extends TestCase
{
    use RefreshDatabase;
    
    private HierarquiaCacheService $cacheService;
    private $mockTreeBuilder;
    private $mockQueryOptimizer;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar mocks dos serviços dependentes
        $this->mockTreeBuilder = Mockery::mock(HierarchyTreeBuilder::class);
        $this->mockQueryOptimizer = Mockery::mock(HierarchyQueryOptimizer::class);
        
        // Instanciar o serviço com os mocks
        $this->cacheService = new HierarquiaCacheService(
            $this->mockTreeBuilder,
            $this->mockQueryOptimizer
        );
        
        // Limpar cache antes de cada teste
        Cache::flush();
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /** @test */
    public function pode_obter_arvore_hierarquica_do_cache()
    {
        // Arrange
        $expectedTree = [
            1 => [
                'id' => 1,
                'name' => 'João Silva',
                'department' => 'TI',
                'subordinates' => []
            ]
        ];
        
        $this->mockTreeBuilder
            ->shouldReceive('buildCompleteTree')
            ->once()
            ->andReturn($expectedTree);
        
        // Act
        $tree1 = $this->cacheService->getHierarchyTree();
        $tree2 = $this->cacheService->getHierarchyTree(); // Segunda chamada deve usar cache
        
        // Assert
        $this->assertEquals($expectedTree, $tree1);
        $this->assertEquals($expectedTree, $tree2);
        $this->assertTrue(Cache::has('hierarchy:tree:v1'));
    }
    
    /** @test */
    public function pode_obter_subordinados_do_usuario_do_cache()
    {
        // Arrange
        $userId = 1;
        $expectedSubordinates = [
            ['id' => 2, 'name' => 'Maria Santos', 'level' => 1],
            ['id' => 3, 'name' => 'Pedro Costa', 'level' => 1]
        ];
        
        $this->mockTreeBuilder
            ->shouldReceive('buildUserSubordinatesTree')
            ->with($userId, 3)
            ->once()
            ->andReturn($expectedSubordinates);
        
        // Act
        $subordinates1 = $this->cacheService->getUserSubordinates($userId);
        $subordinates2 = $this->cacheService->getUserSubordinates($userId); // Cache hit
        
        // Assert
        $this->assertEquals($expectedSubordinates, $subordinates1);
        $this->assertEquals($expectedSubordinates, $subordinates2);
        $this->assertTrue(Cache::has("hierarchy:user_subordinates:{$userId}"));
    }
    
    /** @test */
    public function pode_obter_gestores_do_usuario_do_cache()
    {
        // Arrange
        $userId = 2;
        $expectedManagers = [
            ['id' => 1, 'name' => 'João Silva', 'level' => 1],
            ['id' => 4, 'name' => 'Ana Oliveira', 'level' => 2]
        ];
        
        $this->mockQueryOptimizer
            ->shouldReceive('getManagersChain')
            ->with($userId, 3)
            ->once()
            ->andReturn(collect($expectedManagers));
        
        // Act
        $managers1 = $this->cacheService->getUserManagers($userId);
        $managers2 = $this->cacheService->getUserManagers($userId); // Cache hit
        
        // Assert
        $this->assertEquals($expectedManagers, $managers1);
        $this->assertEquals($expectedManagers, $managers2);
        $this->assertTrue(Cache::has("hierarchy:user_managers:{$userId}"));
    }
    
    /** @test */
    public function pode_validar_relacao_gestor_subordinado_com_cache()
    {
        // Arrange
        $managerId = 1;
        $subordinateId = 2;
        
        $this->mockQueryOptimizer
            ->shouldReceive('isManagerOfOptimized')
            ->with($managerId, $subordinateId, 2)
            ->once()
            ->andReturn(true);
        
        // Act
        $isManager1 = $this->cacheService->isManagerOf($managerId, $subordinateId);
        $isManager2 = $this->cacheService->isManagerOf($managerId, $subordinateId); // Cache hit
        
        // Assert
        $this->assertTrue($isManager1);
        $this->assertTrue($isManager2);
        $this->assertTrue(Cache::has("hierarchy:manager_validation:{$managerId}:{$subordinateId}"));
    }
    
    /** @test */
    public function pode_obter_usuarios_ativos_com_roles_do_cache()
    {
        // Arrange
        $expectedUsers = [
            ['id' => 1, 'name' => 'João Silva', 'roles' => ['admin']],
            ['id' => 2, 'name' => 'Maria Santos', 'roles' => ['user']]
        ];
        
        $this->mockQueryOptimizer
            ->shouldReceive('getActiveUsersWithRoles')
            ->with([])
            ->once()
            ->andReturn(collect($expectedUsers));
        
        // Act
        $users1 = $this->cacheService->getActiveUsersWithRoles();
        $users2 = $this->cacheService->getActiveUsersWithRoles(); // Cache hit
        
        // Assert
        $this->assertEquals($expectedUsers, $users1->toArray());
        $this->assertEquals($expectedUsers, $users2->toArray());
        $this->assertTrue(Cache::has('hierarchy:active_users_roles:all'));
    }
    
    /** @test */
    public function pode_invalidar_cache_do_usuario()
    {
        // Arrange
        $userId = 1;
        
        // Preencher cache primeiro
        Cache::put("hierarchy:user_subordinates:{$userId}", ['data'], 900);
        Cache::put("hierarchy:user_managers:{$userId}", ['data'], 900);
        Cache::put("hierarchy:manager_validation:{$userId}:2", true, 300);
        
        // Act
        $this->cacheService->invalidateUserCache($userId);
        
        // Assert
        $this->assertFalse(Cache::has("hierarchy:user_subordinates:{$userId}"));
        $this->assertFalse(Cache::has("hierarchy:user_managers:{$userId}"));
        $this->assertFalse(Cache::has("hierarchy:manager_validation:{$userId}:2"));
    }
    
    /** @test */
    public function pode_invalidar_cache_completo()
    {
        // Arrange
        Cache::put('hierarchy:tree:v1', ['data'], 1800);
        Cache::put('hierarchy:active_users_roles:all', ['data'], 1200);
        Cache::put('hierarchy:user_subordinates:1', ['data'], 900);
        
        // Act
        $this->cacheService->invalidateFullCache('test_reason');
        
        // Assert
        $this->assertFalse(Cache::has('hierarchy:tree:v1'));
        $this->assertFalse(Cache::has('hierarchy:active_users_roles:all'));
        $this->assertFalse(Cache::has('hierarchy:user_subordinates:1'));
    }
    
    /** @test */
    public function pode_fazer_warm_do_cache()
    {
        // Arrange
        $expectedTree = ['tree_data'];
        $expectedUsers = [['user_data']];
        
        $this->mockTreeBuilder
            ->shouldReceive('buildCompleteTree')
            ->once()
            ->andReturn($expectedTree);
            
        $this->mockQueryOptimizer
            ->shouldReceive('getActiveUsersWithRoles')
            ->with([])
            ->once()
            ->andReturn(collect($expectedUsers));
        
        // Act
        $result = $this->cacheService->warmCache();
        
        // Assert
        $this->assertTrue($result);
        $this->assertTrue(Cache::has('hierarchy:tree:v1'));
        $this->assertTrue(Cache::has('hierarchy:active_users_roles:all'));
    }
    
    /** @test */
    public function pode_obter_metricas_do_cache()
    {
        // Arrange
        Cache::put('hierarchy:metrics', [
            'cache_hits' => 10,
            'cache_misses' => 2,
            'total_requests' => 12
        ], 300);
        
        // Act
        $metrics = $this->cacheService->getCacheMetrics();
        
        // Assert
        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('cache_hits', $metrics);
        $this->assertArrayHasKey('cache_misses', $metrics);
        $this->assertArrayHasKey('hit_rate', $metrics);
        $this->assertEquals(83.33, $metrics['hit_rate']);
    }
    
    /** @test */
    public function pode_limpar_todo_cache()
    {
        // Arrange
        Cache::put('hierarchy:tree:v1', ['data'], 1800);
        Cache::put('hierarchy:user_subordinates:1', ['data'], 900);
        Cache::put('other:cache:key', ['data'], 600); // Não deve ser removido
        
        // Act
        $result = $this->cacheService->clearAllCache();
        
        // Assert
        $this->assertTrue($result);
        $this->assertFalse(Cache::has('hierarchy:tree:v1'));
        $this->assertFalse(Cache::has('hierarchy:user_subordinates:1'));
        $this->assertTrue(Cache::has('other:cache:key')); // Deve permanecer
    }
    
    /** @test */
    public function incrementa_metricas_corretamente()
    {
        // Arrange - Limpar métricas
        Cache::forget('hierarchy:metrics');
        
        // Act - Simular cache hit e miss
        $this->cacheService->getHierarchyTree(); // Miss (primeira vez)
        $this->cacheService->getHierarchyTree(); // Hit (segunda vez)
        
        // Assert
        $metrics = $this->cacheService->getCacheMetrics();
        $this->assertGreaterThan(0, $metrics['total_requests']);
    }
    
    /** @test */
    public function trata_erro_graciosamente_quando_cache_falha()
    {
        // Arrange
        Cache::shouldReceive('get')
            ->andThrow(new \Exception('Cache connection failed'));
            
        $this->mockTreeBuilder
            ->shouldReceive('buildCompleteTree')
            ->once()
            ->andReturn(['fallback_data']);
        
        // Act & Assert - Não deve lançar exceção
        $result = $this->cacheService->getHierarchyTree();
        $this->assertEquals(['fallback_data'], $result);
    }
}
