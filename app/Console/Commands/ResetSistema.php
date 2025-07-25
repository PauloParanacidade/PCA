<?php
//alterar yes para y e no para n
//automatizar as respostas y e n para não precisar digitar
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

        $this->info('🔄 Resetando o sistema...');

        // migrate:fresh recomendado apenas em dev, mas depende do caso
        if ($env !== 'production') {
            $this->call('migrate:fresh');
            $this->info('✅ Tabelas dropadas e recriadas via migrate:fresh.');
        } else {
            $this->info('⚠️ migrate:fresh ignorado em produção para evitar perda de dados.');
        }

        $this->call('ldap:import');
        $this->info('✅ LDAP importado.');

        $this->call('db:seed');
        $this->info('✅ Seeders executados.');

        // composer dump-autoload sempre
        $this->info('🔄 Executando composer dump-autoload...');
        shell_exec('composer dump-autoload');
        $this->info('✅ Autoload do Composer regenerado.');

        if ($env === 'production') {
            // Limpa e gera cache para produção
            $this->call('config:clear');
            $this->call('cache:clear');
            $this->call('route:clear');
            $this->call('view:clear');

            $this->call('config:cache');
            $this->call('route:cache');
            $this->call('view:cache');

            $url = 'https://pca.paranacidade.org.br/';
        } else {
            // Em dev, limpezas de cache opcionais - aqui decidi não fazer nada para evitar atrapalhar debug
            $this->info('⚠️ Pulando limpeza e geração de cache para evitar problemas em desenvolvimento.');
            $url = 'http://localhost:8000/';
        }

        $this->info("🌐 Abrindo o sistema no navegador em $url");
        shell_exec("start chrome $url");

        $this->info("\n🎯 Sistema resetado com sucesso.");
    }
}
