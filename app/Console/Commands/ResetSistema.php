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

        // Verifica se a pasta vendor existe
        if (!is_dir(base_path('vendor'))) {
            $this->error('🚨 A pasta "vendor" não existe. Execute "composer install" antes de resetar o sistema.');
            return;
        }

        // Confirmação do usuário
        if (! $this->confirm('⚠️ Tem certeza que deseja resetar o sistema? Isso apagará e recriará todas as tabelas. [y/N]', false)) {
            $this->warn('❌ Operação cancelada.');
            return;
        }

        $this->info('🔄 Iniciando processo de reset...');

        $executarMigrateFresh = false;

        // Segurança para produção
        if ($env === 'production') {
            $this->error('🚨 AMBIENTE DE PRODUÇÃO DETECTADO!');
            $this->warn('⚠️ Atenção: executar "migrate:fresh" apagará TODOS OS DADOS.');
            if ($this->confirm('❗❗❗ Tem absoluta certeza que deseja apagar todos os dados? [y/N]', false)) {
                if ($this->confirm('❗❗❗ ÚLTIMA CONFIRMAÇÃO: Isso irá DESTRUIR todos os dados. Continuar? [y/N]', false)) {
                    $executarMigrateFresh = true;
                } else {
                    $this->info('⏩ migrate:fresh cancelado por segurança.');
                }
            } else {
                $this->info('⏩ migrate:fresh ignorado por segurança.');
            }
        } else {
            // Em dev ou outros ambientes, executar migrate:fresh automaticamente
            $executarMigrateFresh = true;
        }

        // Executa migrate ou verifica migrations pendentes
        if ($executarMigrateFresh) {
            $this->info('🔄 Executando migrate:fresh...');
            $this->call('migrate:fresh');
            $this->info('✅ migrate:fresh finalizado');
        } else {
            $this->info('🔍 Verificando migrations pendentes...');
            $this->call('migrate:status');

            if ($this->confirm('🔄 Deseja executar migrations pendentes? [y/N]', true)) {
                $this->info('🔄 Executando migrations...');
                $this->call('migrate');
                $this->info('✅ Migrations executadas');
            }
        }

        // Importação LDAP
        $this->info('🔄 Iniciando ldap:import...');
        $this->call('ldap:import');
        $this->info('✅ ldap:import finalizado');

        // DB seeding
        $this->info('🔄 Iniciando db:seed...');
        $this->call('db:seed');
        $this->info('✅ db:seed finalizado');

        // Configura coordenador CLC
        $this->info('🔄 Identificando coordenador CLC...');
        $this->call('clc:identificar-coordenador', ['--force' => true]);
        $this->info('✅ Coordenador CLC identificado e configurado');

        // Cache e otimizações
        if ($env === 'production') {
            $this->info('🔄 Limpando caches e gerando cache de configuração/rotas/views...');
            $this->call('optimize:clear');
            $this->call('config:cache');
            $this->call('route:cache');
            $this->call('view:cache');
            $this->info('✅ Cache otimizado para produção');
        } else {
            $this->info('⚠️ Cache não será gerado em ambiente de desenvolvimento.');

            $url = 'http://localhost:8000/';
            $this->info("🌐 Abrindo o sistema no navegador em $url");

            $command = PHP_OS_FAMILY === 'Windows' ? "start chrome \"$url\"" : "xdg-open \"$url\"";
            shell_exec($command);
        }

        $this->info("\n🎯 Sistema resetado com sucesso.");
    }
}
