<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        If (env('APP_ENV') !== 'local') {
            $this->app['request']->server->set('HTTPS', true);
        }
        
        User::observe(UserObserver::class);
        
        // Log de Queries para análise de performance
        $this->setupQueryLogging();
    }
    
    /**
     * Configura o log de queries para análise de performance
     */
    private function setupQueryLogging(): void
    {
        // Só ativa em produção ou quando explicitamente habilitado
        if (config('app.env') === 'production' || config('app.log_queries', false)) {
            DB::listen(function ($query) {
                // Cria o diretório de logs se não existir
                $logPath = storage_path('logs/queries');
                if (!File::exists($logPath)) {
                    File::makeDirectory($logPath, 0755, true);
                }
                
                // Prepara os dados da query
                $queryData = [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time, // em milissegundos
                    'connection' => $query->connectionName,
                    'timestamp' => now()->toISOString(),
                    'user_id' => auth()->id(),
                    'route' => request()->route()?->getName(),
                    'method' => request()->method(),
                    'url' => request()->url()
                ];
                
                // Log em arquivo específico para queries
                Log::build([
                    'driver' => 'single',
                    'path' => storage_path('logs/queries/queries-' . date('Y-m-d') . '.log'),
                ])->info('Query executed', $queryData);
                
                // Log queries lentas (acima de 100ms) em arquivo separado
                if ($query->time > 100) {
                    Log::build([
                        'driver' => 'single',
                        'path' => storage_path('logs/queries/slow-queries-' . date('Y-m-d') . '.log'),
                    ])->warning('Slow query detected', $queryData);
                }
            });
        }
    }
}
