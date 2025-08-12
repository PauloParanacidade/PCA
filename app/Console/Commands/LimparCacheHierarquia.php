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
    protected $signature = 'cache:limpar-hierarquia {--user-id= : ID específico do usuário} {--all : Limpar cache de todos os usuários}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa o cache de hierarquia para otimizar performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $all = $this->option('all');

        if ($userId) {
            // Limpar cache de um usuário específico
            $this->limparCacheUsuario($userId);
        } elseif ($all) {
            // Limpar cache de todos os usuários
            $this->limparCacheTodos();
        } else {
            $this->error('Especifique --user-id ou --all');
            return 1;
        }

        return 0;
    }

    /**
     * Limpa cache de um usuário específico
     */
    private function limparCacheUsuario($userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado.");
            return;
        }

        $cacheKey = "arvore_hierarquica_user_{$userId}";
        $cacheKeyContar = "contar_acompanhar_user_{$userId}";
        
        Cache::forget($cacheKey);
        Cache::forget($cacheKeyContar);
        
        $this->info("Cache limpo para usuário: {$user->name} (ID: {$userId})");
        $this->line("Keys removidas: {$cacheKey}, {$cacheKeyContar}");
    }

    /**
     * Limpa cache de todos os usuários
     */
    private function limparCacheTodos()
    {
        $users = User::where('active', true)->get();
        $count = 0;

        $this->info("Limpando cache de hierarquia para {$users->count()} usuários...");
        
        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            $cacheKey = "arvore_hierarquica_user_{$user->id}";
            $cacheKeyContar = "contar_acompanhar_user_{$user->id}";
            
            Cache::forget($cacheKey);
            Cache::forget($cacheKeyContar);
            
            $count += 2;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Cache limpo com sucesso! {$count} keys removidas.");
    }
}
