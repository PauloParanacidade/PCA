<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HierarchyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Hierarchy API Routes
|--------------------------------------------------------------------------
|
| Rotas para gerenciamento da hierarquia organizacional
|
*/
Route::middleware('auth:sanctum')->prefix('hierarchy')->name('hierarchy.')->group(function () {
    // Obter árvore hierárquica completa
    Route::get('/tree', [HierarchyController::class, 'getHierarchyTree'])
        ->name('tree');
    
    // Rotas relacionadas a usuários específicos
    Route::prefix('users/{userId}')->where(['userId' => '[0-9]+'])->group(function () {
        // Obter subordinados de um usuário
        Route::get('/subordinates', [HierarchyController::class, 'getUserSubordinates'])
            ->name('user.subordinates');
        
        // Obter gerentes de um usuário
        Route::get('/managers', [HierarchyController::class, 'getUserManagers'])
            ->name('user.managers');
        
        // Verificar se usuário é gerente
        Route::get('/is-manager', [HierarchyController::class, 'checkIfUserIsManager'])
            ->name('user.is-manager');
        
        // Limpar cache de usuário específico
        Route::delete('/cache', [HierarchyController::class, 'clearUserCache'])
            ->name('user.clear-cache');
    });
    
    // Obter usuários por departamento
    Route::get('/departments/{department}/users', [HierarchyController::class, 'getUsersByDepartment'])
        ->name('department.users');
    
    // Validação e métricas
    Route::get('/validate', [HierarchyController::class, 'validateHierarchyIntegrity'])
        ->name('validate');
    
    Route::get('/metrics', [HierarchyController::class, 'getCacheMetrics'])
        ->name('metrics');
    
    // Gerenciamento de cache
    Route::post('/cache/warm', [HierarchyController::class, 'warmCache'])
        ->name('cache.warm');
});

/*
|--------------------------------------------------------------------------
| Hierarchy Admin Routes (Require Admin Role)
|--------------------------------------------------------------------------
|
| Rotas administrativas para gerenciamento avançado da hierarquia
|
*/
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/hierarchy')->name('admin.hierarchy.')->group(function () {
    // Validação completa da hierarquia
    Route::get('/validate/full', [HierarchyController::class, 'validateHierarchyIntegrity'])
        ->name('validate.full');
    
    // Métricas detalhadas
    Route::get('/metrics/detailed', [HierarchyController::class, 'getCacheMetrics'])
        ->name('metrics.detailed');
    
    // Aquecimento de cache com diferentes estratégias
    Route::post('/cache/warm/full', function (Request $request) {
        $request->merge(['type' => 'full']);
        return app(HierarchyController::class)->warmCache($request);
    })->name('cache.warm.full');
    
    Route::post('/cache/warm/managers', function (Request $request) {
        $request->merge(['type' => 'managers']);
        return app(HierarchyController::class)->warmCache($request);
    })->name('cache.warm.managers');
    
    Route::post('/cache/warm/departments', function (Request $request) {
        $request->merge(['type' => 'departments']);
        return app(HierarchyController::class)->warmCache($request);
    })->name('cache.warm.departments');
});

/*
|--------------------------------------------------------------------------
| Public Hierarchy Routes (No Auth Required)
|--------------------------------------------------------------------------
|
| Rotas públicas para informações básicas da hierarquia
|
*/
Route::prefix('public/hierarchy')->name('public.hierarchy.')->group(function () {
    // Estrutura básica da organização (sem dados sensíveis)
    Route::get('/structure', function () {
        $cacheService = app(\App\Services\Hierarchy\HierarquiaCacheService::class);
        $tree = $cacheService->getHierarchicalTree();
        
        // Remover dados sensíveis para rota pública
        $publicTree = collect($tree)->map(function ($node) {
            return [
                'id' => $node['id'] ?? null,
                'name' => $node['name'] ?? 'N/A',
                'department' => $node['department'] ?? null,
                'level' => $node['level'] ?? 0,
                'has_subordinates' => !empty($node['subordinates']),
                'subordinates_count' => count($node['subordinates'] ?? [])
            ];
        });
        
        return response()->json([
            'status' => 'success',
            'message' => 'Estrutura organizacional obtida',
            'data' => $publicTree
        ]);
    })->name('structure');
    
    // Estatísticas gerais da organização
    Route::get('/stats', function () {
        $queryOptimizer = app(\App\Services\Hierarchy\HierarchyQueryOptimizer::class);
        
        $stats = [
            'total_users' => \App\Models\User::where('active', true)->count(),
            'total_managers' => $queryOptimizer->getTotalManagersCount(),
            'total_departments' => \App\Models\User::where('active', true)
                ->whereNotNull('department')
                ->distinct('department')
                ->count(),
            'max_hierarchy_depth' => app(\App\Services\Hierarchy\HierarchyTreeBuilder::class)
                ->calculateMaxDepth(),
        ];
        
        return response()->json([
            'status' => 'success',
            'message' => 'Estatísticas organizacionais',
            'data' => $stats
        ]);
    })->name('stats');
});