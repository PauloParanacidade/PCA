<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetSistema extends Command
{
    protected $signature = 'sistema:reset';
    protected $description = 'Dropa e recria tabelas, importa LDAP, roda seeders, abre o sistema no Chrome e executa testes';

    public function handle()
    {
        if (! $this->confirm('⚠️  Tem certeza que deseja resetar o sistema? Isso apagará e recriará todas as tabelas.')) {
            $this->warn('❌ Operação cancelada.');
            return;
        }

        $this->info('🔄 Resetando o sistema...');

        $this->call('migrate:fresh');
        $this->info('✅ Migrations executadas.');

        $this->call('ldap:import');
        $this->info('✅ LDAP importado.');

        $this->call('db:seed');
        $this->info('✅ Seeders executados.');

        $this->info('🌐 Abrindo o sistema no navegador...');
        shell_exec('start chrome http://localhost:8000/ppp/create');

        // $this->call('test');
        // $this->info('✅ Testes automatizados executados.');

        $this->info('🎯 Sistema resetado com sucesso.');
    }
}
