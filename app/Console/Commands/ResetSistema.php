<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

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
            return 1;
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
            $exitCode = Artisan::call('migrate:fresh');
            $this->output->write(Artisan::output());
            if ($exitCode !== 0) {
                $this->error('❌ Falha ao executar migrate:fresh. Abortando.');
                return $exitCode;
            }
            $this->info('✅ Tabelas dropadas e recriadas via migrate:fresh.');
        }

        $exitCode = Artisan::call('ldap:import');
        $this->output->write(Artisan::output());
        if ($exitCode !== 0) {
            $this->error('❌ Falha ao importar LDAP.');
            // Decide se aborta ou continua:
            // return $exitCode;
        } else {
            $this->info('✅ LDAP importado.');
        }

        $exitCode = Artisan::call('db:seed');
        $this->output->write(Artisan::output());
        if ($exitCode !== 0) {
            $this->error('❌ Falha ao executar seeders.');
            // return $exitCode;
        } else {
            $this->info('✅ Seeders executados.');
        }

        $this->info('🔄 Executando composer dump-autoload...');

        // Caminho absoluto do composer - ajuste se necessário
        $composerPath = 'composer';

        // Executa e captura saída/erro
        $output = null;
        $returnVar = null;
        exec("$composerPath dump-autoload 2>&1", $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error('❌ Falha ao executar composer dump-autoload:');
            foreach ($output as $line) {
                $this->error($line);
            }
            // Decide continuar ou abortar
            // return $returnVar;
        } else {
            $this->info('✅ Autoload do Composer regenerado.');
        }

        if ($env === 'production') {
            $this->info('🔄 Limpando e gerando cache...');
            $commands = ['config:clear', 'cache:clear', 'route:clear', 'view:clear', 'config:cache', 'route:cache', 'view:cache'];

            foreach ($commands as $cmd) {
                $exitCode = Artisan::call($cmd);
                $this->output->write(Artisan::output());
                if ($exitCode !== 0) {
                    $this->error("❌ Falha ao executar $cmd.");
                    // Opcional: abortar ou continuar
                }
            }

            $url = config('app.url');
            $this->info("🌐 Ambiente produção detectado. URL: $url");

            $this->info('⚠️ Ignorando abertura automática do navegador em produção.');
        } else {
            $this->info('⚠️ Ambiente desenvolvimento detectado.');
            $this->info('⚠️ Cache não será gerado para evitar problemas com atualizações.');
            $url = 'http://localhost:8000/';
            $this->info("🌐 Abrindo o sistema no navegador em $url");
            shell_exec("start chrome $url");  // Só funciona em Windows/dev local
        }

        $this->info("\n🎯 Sistema resetado com sucesso.");

        return 0;
    }
}
