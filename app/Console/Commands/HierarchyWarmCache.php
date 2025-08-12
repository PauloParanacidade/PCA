<?php

namespace App\Console\Commands;

use App\Services\Hierarchy\HierarquiaCacheService;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HierarchyWarmCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hierarchy:warm-cache 
                            {--full : Warm all hierarchy cache}
                            {--managers : Warm cache for managers only}
                            {--departments : Warm cache by departments}
                            {--batch-size=100 : Number of users to process in each batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm hierarchy cache data for better performance';

    /**
     * Execute the console command.
     */
    public function handle(HierarquiaCacheService $cacheService)
    {
        $full = $this->option('full');
        $managers = $this->option('managers');
        $departments = $this->option('departments');
        $batchSize = (int) $this->option('batch-size');

        $startTime = microtime(true);
        $this->info('Starting cache warming process...');

        if ($full) {
            $this->warmFullCache($cacheService, $batchSize);
        } elseif ($managers) {
            $this->warmManagersCache($cacheService, $batchSize);
        } elseif ($departments) {
            $this->warmDepartmentsCache($cacheService, $batchSize);
        } else {
            // Default: warm essential cache
            $this->warmEssentialCache($cacheService);
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        $this->info("Cache warming completed in {$duration} seconds.");
        $this->showCacheMetrics($cacheService);

        return 0;
    }

    /**
     * Warm full hierarchy cache
     */
    private function warmFullCache(HierarquiaCacheService $cacheService, int $batchSize)
    {
        $this->info('Warming full hierarchy cache...');
        
        // Warm complete hierarchy tree
        $cacheService->getHierarchyTree();
        $this->line('✓ Complete hierarchy tree cached');
        
        // Warm cache for all active users
        $totalUsers = User::where('active', true)->count();
        $this->info("Processing {$totalUsers} active users...");
        
        $bar = $this->output->createProgressBar($totalUsers);
        $bar->start();
        
        User::where('active', true)
            ->chunk($batchSize, function ($users) use ($cacheService, $bar) {
                foreach ($users as $user) {
                    // Warm subordinates cache
                    $cacheService->getUserSubordinates($user->id);
                    // Warm manager chain cache
                    $cacheService->getUserManagers($user->id);
                    $bar->advance();
                }
            });
        
        $bar->finish();
        $this->line('');
        $this->info('Full cache warming completed.');
    }

    /**
     * Warm cache for managers only
     */
    private function warmManagersCache(HierarquiaCacheService $cacheService, int $batchSize)
    {
        $this->info('Warming cache for managers...');
        
        $managers = User::where('active', true)
            ->whereIn('id', function ($query) {
                $query->select('manager')
                    ->from('users')
                    ->whereNotNull('manager')
                    ->distinct();
            })
            ->get();
        
        $totalManagers = $managers->count();
        $this->info("Processing {$totalManagers} managers...");
        
        $bar = $this->output->createProgressBar($totalManagers);
        $bar->start();
        
        foreach ($managers as $manager) {
            $cacheService->getUserSubordinates($manager->id);
            $cacheService->getUserManagers($manager->id);
            $bar->advance();
        }
        
        $bar->finish();
        $this->line('');
        $this->info('Managers cache warming completed.');
    }

    /**
     * Warm cache by departments
     */
    private function warmDepartmentsCache(HierarquiaCacheService $cacheService, int $batchSize)
    {
        $this->info('Warming cache by departments...');
        
        $departments = User::where('active', true)
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department');
        
        $totalDepartments = $departments->count();
        $this->info("Processing {$totalDepartments} departments...");
        
        $bar = $this->output->createProgressBar($totalDepartments);
        $bar->start();
        
        foreach ($departments as $department) {
            $cacheService->getUsersByDepartmentWithHierarchy($department);
            $bar->advance();
        }
        
        $bar->finish();
        $this->line('');
        $this->info('Departments cache warming completed.');
    }

    /**
     * Warm essential cache (default)
     */
    private function warmEssentialCache(HierarquiaCacheService $cacheService)
    {
        $this->info('Warming essential hierarchy cache...');
        
        // Warm complete hierarchy tree
        $cacheService->getHierarchicalTree();
        $this->line('✓ Complete hierarchy tree cached');
        
        // Warm cache for top-level managers
        $topManagers = User::where('active', true)
            ->whereNull('manager')
            ->get();
        
        $this->info("Processing {$topManagers->count()} top-level managers...");
        
        foreach ($topManagers as $manager) {
            $cacheService->getUserSubordinates($manager->id);
        }
        
        $this->info('Essential cache warming completed.');
    }

    /**
     * Show cache metrics after warming
     */
    private function showCacheMetrics(HierarquiaCacheService $cacheService)
    {
        $metrics = $cacheService->getCacheMetrics();
        
        $this->info('=== Cache Metrics After Warming ===');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Cache Hits', number_format($metrics['hits'])],
                ['Cache Misses', number_format($metrics['misses'])],
                ['Hit Rate', number_format($metrics['hit_rate'], 2) . '%'],
                ['Total Requests', number_format($metrics['total_requests'])],
                ['Memory Usage', $this->formatBytes($metrics['memory_usage'])],
            ]
        );
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
