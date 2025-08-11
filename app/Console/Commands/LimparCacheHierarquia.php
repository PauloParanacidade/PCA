<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class LimparCacheHierarquia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-hierarquia {--user= : ID do usuário específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa o cache das árvores hierárquicas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user');
        
        if ($userId) {
            // Limpar cache de um usuário específico
            $cacheKey = "arvore_hierarquica_user_{$userId}";
            Cache::forget($cacheKey);
            
            $this->info("Cache da árvore hierárquica do usuário {$userId} foi limpo.");
        } else {
            // Limpar cache de todos os usuários
            $usuarios = User::where('active', true)->pluck('id');
            $count = 0;
            
            foreach ($usuarios as $id) {
                $cacheKey = "arvore_hierarquica_user_{$id}";
                if (Cache::forget($cacheKey)) {
                    $count++;
                }
            }
            
            $this->info("Cache das árvores hierárquicas foi limpo para {$count} usuários.");
        }
        
        return 0;
    }
}