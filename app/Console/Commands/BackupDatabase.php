<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Realiza backup do banco de dados MySQL';

    public function handle()
    {
        // Nome do arquivo de backup
        $backupFileName = 'backup_' . date('Y-m-d_His') . '.sql';

        // Caminho para o diretório de destino
        $backupPath = '/mnt/arquivos_dsspro/backupMysqlNormas/' . $backupFileName;

        // Comando para executar o backup
        $command = sprintf(
            'mysqldump -u%s -p%s %s > %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            $backupPath
        );

        // Executa o comando
        system($command);

        $this->info('Backup do banco de dados realizado com sucesso.');
    }
}
