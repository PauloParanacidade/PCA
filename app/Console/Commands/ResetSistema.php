<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetSistema extends Command
{
    protected $signature = 'sistema:reset';
    protected $description = 'Reset completo com ambiente condicional para produção e desenvolvimento';
    
    public function handle()
    {
        $env = app()->environment();
        $this->info("🌍 Ambiente detectado: $env");
        
        if (! $this->confirm('⚠️ Tem certeza que deseja resetar o sistema? Isso apagará e recriará todas as tabelas. [y/N]', false)) {
            $this->warn('❌ Operação cancelada.');
            return;
        }
        
        $this->info('🔄 Iniciando processo de reset...');
        
        $executarMigrateFresh = false;
        
        if ($env !== 'production') {
            $executarMigrateFresh = true;
        } else {
            $this->warn('⚠️ Atenção: você está em produção. Executar "migrate:fresh" irá APAGAR todos os dados.');
            if ($this->confirm('❗ Deseja mesmo continuar com migrate:fresh em produção? [y/N]', false)) {
                $executarMigrateFresh = true;
            } else {
                $this->info('⏩ migrate:fresh ignorado por segurança.');
            }
        }
        
        if ($executarMigrateFresh) {
            $this->info('🔄 Iniciando migrate:fresh');
            $this->call('migrate:fresh');
            $this->info('✅ migrate:fresh finalizado');
        }
        
        $this->info('🔄 Iniciando ldap:import');
        $this->call('ldap:import');
        $this->info('✅ ldap:import finalizado');
        
        $this->info('🔄 Iniciando db:seed');
        $this->call('db:seed');
        $this->info('✅ db:seed finalizado');
        
        $this->info('🔄 Executando composer dump-autoload...');
        $output = shell_exec('composer dump-autoload 2>&1');
        if ($output === null) {
            $this->warn('⚠️ Falha ao executar composer dump-autoload. Verifique o PATH do composer e permissões.');
        } else {
            $this->info('✅ Autoload do Composer regenerado.');
        }
        
        if ($env === 'production') {
            
            $this->call('optimize:clear'); // limpa todos os caches (config, route, view, cache geral, event)
            
            $this->call('config:cache');
            $this->call('route:cache');
            $this->call('view:cache');
            
        } else {
            $this->info('⚠️ Cache não será gerado em ambiente de desenvolvimento para evitar problemas de atualização.');
            $url = 'http://localhost:8000/';
            
            $this->info("🌐 Abrindo o sistema no navegador em $url");
            // No Windows pode ser start, no Linux xdg-open ou open
            $command = PHP_OS_FAMILY === 'Windows' ? "start chrome \"$url\"" : "xdg-open \"$url\"";
            shell_exec($command);
        }
        
        $this->info("\n🎯 Sistema resetado com sucesso.");
    }
}
