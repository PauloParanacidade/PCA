<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClcService;
use Illuminate\Support\Facades\Log;

class IdentificarCoordenadorClc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clc:identificar-coordenador {--force : Força a re-identificação mesmo se já houver um coordenador}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Identifica automaticamente o coordenador CLC e atribui a role correspondente';

    /**
     * Execute the console command.
     */
    public function handle(ClcService $clcService)
    {
        $this->info('🔍 Iniciando identificação do coordenador CLC...');
        
        try {
            // Limpar cache se forçado
            if ($this->option('force')) {
                $clcService->limparCacheCoordenadorClc();
                $this->info('🗑️ Cache limpo - forçando nova identificação');
            }
            
            // Identificar coordenador
            $coordenador = $clcService->identificarCoordenadorClc();
            
            if (!$coordenador) {
                $this->error('❌ Coordenador CLC não encontrado!');
                $this->warn('Verifique se existe algum usuário com manager que tenha sigla CLC.');
                return Command::FAILURE;
            }
            
            $this->info("✅ Coordenador CLC identificado: {$coordenador->name} (ID: {$coordenador->id})");
            
            // Atribuir role
            $sucesso = $clcService->atribuirRoleClc();
            
            if ($sucesso) {
                $this->info('✅ Role CLC atribuída com sucesso!');
                $this->info('🎯 O coordenador CLC agora pode visualizar todos os PPPs na Visão Geral.');
                return Command::SUCCESS;
            } else {
                $this->error('❌ Erro ao atribuir role CLC');
                return Command::FAILURE;
            }
            
        } catch (\Throwable $ex) {
            $this->error('❌ Erro durante execução: ' . $ex->getMessage());
            Log::error('Erro no comando identificar-coordenador-clc: ' . $ex->getMessage());
            return Command::FAILURE;
        }
    }
}
