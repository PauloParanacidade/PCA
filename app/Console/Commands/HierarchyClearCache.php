<?php

namespace App\Console\Commands;

use App\Services\Hierarchy\HierarquiaCacheService;
use Illuminate\Console\Command;

class HierarchyClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hierarchy:clear-cache 
                            {--user= : Clear cache for specific user ID}
                            {--all : Clear all hierarchy cache}
                            {--metrics : Show cache metrics before clearing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear hierarchy cache data';

    /**
     * Execute the console command.
     */
    public function handle(HierarquiaCacheService $cacheService)
    {
        $userId = $this->option('user');
        $clearAll = $this->option('all');
        $showMetrics = $this->option('metrics');

        // Show metrics before clearing if requested
        if ($showMetrics) {
            $this->showMetrics($cacheService);
        }

        if ($userId) {
            // Clear cache for specific user
            $cacheService->invalidateUserCache($userId);
            $this->info("Cache cleared for user ID: {$userId}");
        } elseif ($clearAll) {
            // Clear all hierarchy cache
            $cacheService->clearAllCache();
            $this->info('All hierarchy cache cleared successfully.');
        } else {
            // Show help if no options provided
            $this->error('Please specify either --user=ID or --all option.');
            $this->line('Examples:');
            $this->line('  php artisan hierarchy:clear-cache --user=123');
            $this->line('  php artisan hierarchy:clear-cache --all');
            $this->line('  php artisan hierarchy:clear-cache --all --metrics');
            return 1;
        }

        return 0;
    }

    /**
     * Show cache metrics
     */
    private function showMetrics(HierarquiaCacheService $cacheService)
    {
        $metrics = $cacheService->getCacheMetrics();
        
        $this->info('=== Cache Metrics ===');
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
        $this->line('');
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
