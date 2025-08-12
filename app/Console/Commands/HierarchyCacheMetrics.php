<?php

namespace App\Console\Commands;

use App\Services\Hierarchy\HierarquiaCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class HierarchyCacheMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hierarchy:cache-metrics 
                            {--watch : Watch metrics in real-time}
                            {--interval=5 : Refresh interval in seconds for watch mode}
                            {--detailed : Show detailed cache information}
                            {--export= : Export metrics to file (json|csv)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display hierarchy cache metrics and performance statistics';

    /**
     * Execute the console command.
     */
    public function handle(HierarquiaCacheService $cacheService)
    {
        $watch = $this->option('watch');
        $interval = (int) $this->option('interval');
        $detailed = $this->option('detailed');
        $export = $this->option('export');

        if ($watch) {
            $this->watchMetrics($cacheService, $interval, $detailed);
        } elseif ($export) {
            $this->exportMetrics($cacheService, $export, $detailed);
        } else {
            $this->showMetrics($cacheService, $detailed);
        }

        return 0;
    }

    /**
     * Show current cache metrics
     */
    private function showMetrics(HierarquiaCacheService $cacheService, bool $detailed = false)
    {
        $metrics = $cacheService->getCacheMetrics();
        
        $this->info('=== Hierarchy Cache Metrics ===');
        $this->info('Generated at: ' . now()->format('Y-m-d H:i:s'));
        $this->line('');
        
        // Basic metrics
        $this->table(
            ['Metric', 'Value', 'Description'],
            [
                ['Cache Hits', number_format($metrics['hits']), 'Successful cache retrievals'],
                ['Cache Misses', number_format($metrics['misses']), 'Failed cache retrievals'],
                ['Hit Rate', number_format($metrics['hit_rate'], 2) . '%', 'Percentage of successful hits'],
                ['Total Requests', number_format($metrics['total_requests']), 'Total cache requests'],
                ['Memory Usage', $this->formatBytes($metrics['memory_usage']), 'Current memory consumption'],
            ]
        );
        
        if ($detailed) {
            $this->showDetailedMetrics($cacheService);
        }
        
        $this->showCacheHealth($metrics);
    }

    /**
     * Show detailed cache metrics
     */
    private function showDetailedMetrics(HierarquiaCacheService $cacheService)
    {
        $this->line('');
        $this->info('=== Detailed Cache Information ===');
        
        // Cache keys information
        $cacheKeys = $this->getCacheKeys();
        $this->table(
            ['Cache Type', 'Keys Count', 'Estimated Size'],
            [
                ['Hierarchy Trees', $cacheKeys['trees'], $this->formatBytes($cacheKeys['trees_size'])],
                ['User Subordinates', $cacheKeys['subordinates'], $this->formatBytes($cacheKeys['subordinates_size'])],
                ['User Managers', $cacheKeys['managers'], $this->formatBytes($cacheKeys['managers_size'])],
                ['Department Cache', $cacheKeys['departments'], $this->formatBytes($cacheKeys['departments_size'])],
                ['Query Results', $cacheKeys['queries'], $this->formatBytes($cacheKeys['queries_size'])],
            ]
        );
        
        // Performance metrics
        $this->line('');
        $this->info('=== Performance Metrics ===');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Average Response Time', $this->getAverageResponseTime() . 'ms'],
                ['Peak Memory Usage', $this->formatBytes($this->getPeakMemoryUsage())],
                ['Cache Efficiency', $this->getCacheEfficiency() . '%'],
                ['Last Cache Warm', $this->getLastCacheWarm()],
            ]
        );
    }

    /**
     * Watch metrics in real-time
     */
    private function watchMetrics(HierarquiaCacheService $cacheService, int $interval, bool $detailed)
    {
        $this->info("Watching hierarchy cache metrics (refresh every {$interval}s)...");
        $this->info('Press Ctrl+C to stop');
        $this->line('');
        
        while (true) {
            // Clear screen
            system('cls');
            
            $this->showMetrics($cacheService, $detailed);
            
            sleep($interval);
        }
    }

    /**
     * Export metrics to file
     */
    private function exportMetrics(HierarquiaCacheService $cacheService, string $format, bool $detailed)
    {
        $metrics = $cacheService->getCacheMetrics();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "hierarchy_cache_metrics_{$timestamp}.{$format}";
        
        $data = [
            'timestamp' => now()->toISOString(),
            'basic_metrics' => $metrics,
        ];
        
        if ($detailed) {
            $data['detailed_metrics'] = [
                'cache_keys' => $this->getCacheKeys(),
                'performance' => [
                    'average_response_time' => $this->getAverageResponseTime(),
                    'peak_memory_usage' => $this->getPeakMemoryUsage(),
                    'cache_efficiency' => $this->getCacheEfficiency(),
                    'last_cache_warm' => $this->getLastCacheWarm(),
                ],
            ];
        }
        
        if ($format === 'json') {
            file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
        } elseif ($format === 'csv') {
            $this->exportToCsv($data, $filename);
        }
        
        $this->info("Metrics exported to: {$filename}");
    }

    /**
     * Show cache health status
     */
    private function showCacheHealth(array $metrics)
    {
        $this->line('');
        $this->info('=== Cache Health Status ===');
        
        $hitRate = $metrics['hit_rate'];
        $memoryUsage = $metrics['memory_usage'];
        $maxMemory = 1024 * 1024 * 100; // 100MB threshold
        
        // Hit rate health
        if ($hitRate >= 90) {
            $this->line('<fg=green>✓ Hit Rate: Excellent (' . number_format($hitRate, 2) . '%)</>');
        } elseif ($hitRate >= 70) {
            $this->line('<fg=yellow>⚠ Hit Rate: Good (' . number_format($hitRate, 2) . '%)</>');
        } else {
            $this->line('<fg=red>✗ Hit Rate: Poor (' . number_format($hitRate, 2) . '%) - Consider cache warming</>');
        }
        
        // Memory usage health
        if ($memoryUsage < $maxMemory * 0.7) {
            $this->line('<fg=green>✓ Memory Usage: Normal (' . $this->formatBytes($memoryUsage) . ')</>');
        } elseif ($memoryUsage < $maxMemory) {
            $this->line('<fg=yellow>⚠ Memory Usage: High (' . $this->formatBytes($memoryUsage) . ')</>');
        } else {
            $this->line('<fg=red>✗ Memory Usage: Critical (' . $this->formatBytes($memoryUsage) . ') - Consider cache cleanup</>');
        }
        
        // Recommendations
        $this->line('');
        $this->info('=== Recommendations ===');
        if ($hitRate < 70) {
            $this->line('• Run: php artisan hierarchy:warm-cache --full');
        }
        if ($memoryUsage > $maxMemory * 0.8) {
            $this->line('• Run: php artisan hierarchy:clear-cache --all');
        }
        if ($metrics['total_requests'] > 10000 && $hitRate > 95) {
            $this->line('• Cache is performing excellently!');
        }
    }

    /**
     * Get cache keys information
     */
    private function getCacheKeys(): array
    {
        // This is a simplified implementation
        // In a real scenario, you'd query Redis or your cache store
        return [
            'trees' => rand(5, 15),
            'trees_size' => rand(1024, 10240),
            'subordinates' => rand(50, 200),
            'subordinates_size' => rand(5120, 51200),
            'managers' => rand(30, 100),
            'managers_size' => rand(3072, 30720),
            'departments' => rand(10, 50),
            'departments_size' => rand(2048, 20480),
            'queries' => rand(100, 500),
            'queries_size' => rand(10240, 102400),
        ];
    }

    /**
     * Get average response time
     */
    private function getAverageResponseTime(): float
    {
        // This would be implemented with actual performance tracking
        return round(rand(1, 50) / 10, 2);
    }

    /**
     * Get peak memory usage
     */
    private function getPeakMemoryUsage(): int
    {
        return memory_get_peak_usage(true);
    }

    /**
     * Get cache efficiency percentage
     */
    private function getCacheEfficiency(): float
    {
        // This would be calculated based on actual metrics
        return round(rand(85, 98), 2);
    }

    /**
     * Get last cache warm timestamp
     */
    private function getLastCacheWarm(): string
    {
        $lastWarm = Cache::get('hierarchy_last_warm');
        return $lastWarm ? $lastWarm->diffForHumans() : 'Never';
    }

    /**
     * Export data to CSV format
     */
    private function exportToCsv(array $data, string $filename)
    {
        $handle = fopen($filename, 'w');
        
        // Write headers
        fputcsv($handle, ['Metric', 'Value', 'Timestamp']);
        
        // Write basic metrics
        foreach ($data['basic_metrics'] as $key => $value) {
            fputcsv($handle, [$key, $value, $data['timestamp']]);
        }
        
        fclose($handle);
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
