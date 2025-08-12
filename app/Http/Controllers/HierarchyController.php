<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Hierarchy\HierarquiaCacheService;
use App\Services\Hierarchy\HierarchyTreeBuilder;
use App\Services\Hierarchy\HierarchyQueryOptimizer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HierarchyController extends Controller
{
    protected HierarquiaCacheService $cacheService;
    protected HierarchyTreeBuilder $treeBuilder;
    protected HierarchyQueryOptimizer $queryOptimizer;

    public function __construct(
        HierarquiaCacheService $cacheService,
        HierarchyTreeBuilder $treeBuilder,
        HierarchyQueryOptimizer $queryOptimizer
    ) {
        $this->cacheService = $cacheService;
        $this->treeBuilder = $treeBuilder;
        $this->queryOptimizer = $queryOptimizer;
    }

    /**
     * Obter árvore hierárquica completa
     */
    public function getHierarchyTree(): JsonResponse
    {
        try {
            $tree = $this->cacheService->getHierarchicalTree();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Árvore hierárquica obtida com sucesso',
                'data' => $tree
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter árvore hierárquica', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Obter subordinados de um usuário
     */
    public function getUserSubordinates(Request $request, int $userId): JsonResponse
    {
        $validator = Validator::make(['user_id' => $userId], [
            'user_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $includeIndirect = $request->boolean('include_indirect', true);
            
            if ($includeIndirect) {
                $subordinates = $this->cacheService->getUserSubordinates($userId);
            } else {
                $subordinates = $this->queryOptimizer->getDirectSubordinates($userId);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Subordinados obtidos com sucesso',
                'data' => [
                    'user_id' => $userId,
                    'include_indirect' => $includeIndirect,
                    'subordinates' => $subordinates,
                    'total_count' => count($subordinates)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter subordinados', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao obter subordinados'
            ], 500);
        }
    }

    /**
     * Obter gerentes de um usuário
     */
    public function getUserManagers(int $userId): JsonResponse
    {
        $validator = Validator::make(['user_id' => $userId], [
            'user_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuário inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $managers = $this->cacheService->getUserManagers($userId);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Cadeia de gerentes obtida com sucesso',
                'data' => [
                    'user_id' => $userId,
                    'managers' => $managers,
                    'hierarchy_level' => count($managers)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter gerentes', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao obter cadeia de gerentes'
            ], 500);
        }
    }

    /**
     * Verificar se usuário é gerente
     */
    public function checkIfUserIsManager(int $userId): JsonResponse
    {
        try {
            $isManager = $this->queryOptimizer->isUserManager($userId);
            $subordinatesCount = 0;
            
            if ($isManager) {
                $subordinates = $this->cacheService->getUserSubordinates($userId);
                $subordinatesCount = count($subordinates);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Verificação realizada com sucesso',
                'data' => [
                    'user_id' => $userId,
                    'is_manager' => $isManager,
                    'subordinates_count' => $subordinatesCount
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar se usuário é gerente', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro na verificação'
            ], 500);
        }
    }

    /**
     * Obter usuários por departamento com hierarquia
     */
    public function getUsersByDepartment(Request $request, string $department): JsonResponse
    {
        $validator = Validator::make(['department' => $department], [
            'department' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Departamento inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $users = $this->cacheService->getUsersByDepartmentWithHierarchy($department);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Usuários do departamento obtidos com sucesso',
                'data' => [
                    'department' => $department,
                    'users' => $users,
                    'total_count' => count($users)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter usuários por departamento', [
                'department' => $department,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao obter usuários do departamento'
            ], 500);
        }
    }

    /**
     * Validar integridade da hierarquia
     */
    public function validateHierarchyIntegrity(): JsonResponse
    {
        try {
            $validation = $this->treeBuilder->validateTreeIntegrity();
            
            $status = $validation['is_valid'] ? 'success' : 'warning';
            $message = $validation['is_valid'] 
                ? 'Hierarquia está íntegra' 
                : 'Problemas encontrados na hierarquia';
            
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $validation
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao validar integridade da hierarquia', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro na validação da hierarquia'
            ], 500);
        }
    }

    /**
     * Obter métricas do cache
     */
    public function getCacheMetrics(): JsonResponse
    {
        try {
            $metrics = $this->cacheService->getCacheMetrics();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Métricas obtidas com sucesso',
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter métricas do cache', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao obter métricas'
            ], 500);
        }
    }

    /**
     * Limpar cache de usuário específico
     */
    public function clearUserCache(int $userId): JsonResponse
    {
        $validator = Validator::make(['user_id' => $userId], [
            'user_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuário inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->cacheService->invalidateUserCache($userId);
            
            Log::info('Cache de usuário limpo via API', [
                'user_id' => $userId,
                'cleared_by' => auth()->id()
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Cache do usuário limpo com sucesso',
                'data' => [
                    'user_id' => $userId,
                    'cleared_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache do usuário', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao limpar cache'
            ], 500);
        }
    }

    /**
     * Aquecer cache da hierarquia
     */
    public function warmCache(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type', 'essential'); // essential, full, managers, departments
            
            $startTime = microtime(true);
            
            switch ($type) {
                case 'full':
                    $this->warmFullCache();
                    break;
                case 'managers':
                    $this->warmManagersCache();
                    break;
                case 'departments':
                    $this->warmDepartmentsCache();
                    break;
                default:
                    $this->warmEssentialCache();
            }
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('Cache aquecido via API', [
                'type' => $type,
                'duration_ms' => $duration,
                'warmed_by' => auth()->id()
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Cache aquecido com sucesso',
                'data' => [
                    'type' => $type,
                    'duration_ms' => $duration,
                    'warmed_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao aquecer cache', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao aquecer cache'
            ], 500);
        }
    }

    /**
     * Aquecer cache essencial
     */
    private function warmEssentialCache(): void
    {
        // Aquecer árvore completa
        $this->cacheService->getHierarchicalTree();
        
        // Aquecer cache dos gerentes de topo
        $topManagers = $this->queryOptimizer->getTopLevelManagers();
        foreach ($topManagers as $manager) {
            $this->cacheService->getUserSubordinates($manager->id);
        }
    }

    /**
     * Aquecer cache completo
     */
    private function warmFullCache(): void
    {
        // Aquecer árvore completa
        $this->cacheService->getHierarchicalTree();
        
        // Aquecer cache de todos os usuários ativos
        $activeUsers = $this->queryOptimizer->getActiveUsersWithRoles(['manager', 'employee']);
        foreach ($activeUsers as $user) {
            $this->cacheService->getUserSubordinates($user->id);
            $this->cacheService->getUserManagers($user->id);
        }
    }

    /**
     * Aquecer cache dos gerentes
     */
    private function warmManagersCache(): void
    {
        $managers = $this->queryOptimizer->getAllManagers();
        foreach ($managers as $manager) {
            $this->cacheService->getUserSubordinates($manager->id);
            $this->cacheService->getUserManagers($manager->id);
        }
    }

    /**
     * Aquecer cache por departamentos
     */
    private function warmDepartmentsCache(): void
    {
        $departments = $this->queryOptimizer->getAllDepartments();
        foreach ($departments as $department) {
            $this->cacheService->getUsersByDepartmentWithHierarchy($department);
        }
    }
}