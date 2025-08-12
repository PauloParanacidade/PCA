<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Services\ClcService;
use Illuminate\Support\Facades\Log;

class ClcRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔍 Iniciando identificação automática do coordenador CLC...');
        
        try {
            $clcService = app(ClcService::class);
            
            // Identificar coordenador
            $coordenador = $clcService->identificarCoordenadorClc();
            
            if (!$coordenador) {
                $this->command->warn('❌ Coordenador CLC não encontrado!');
                $this->command->warn('Verifique se existe algum usuário com manager que tenha sigla CLC.');
                return;
            }
            
            $this->command->info("✅ Coordenador CLC identificado: {$coordenador->name} (ID: {$coordenador->id})");
            
            // Atribuir role
            $sucesso = $clcService->atribuirRoleClc();
            
            if ($sucesso) {
                $this->command->info('✅ Role CLC atribuída com sucesso!');
                $this->command->info('🎯 O coordenador CLC agora pode visualizar todos os PPPs na Visão Geral.');
            } else {
                $this->command->error('❌ Erro ao atribuir role CLC');
            }
            
        } catch (\Throwable $ex) {
            $this->command->error('❌ Erro durante execução: ' . $ex->getMessage());
            Log::error('Erro no ClcRoleSeeder: ' . $ex->getMessage());
        }
    }
}
