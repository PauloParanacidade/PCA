<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration para criar a tabela de histórico dos PPPs.
     * Registra todas as mudanças de status e ações realizadas nos PPPs.
     */
    public function up(): void
    {
        Schema::create('ppp_historicos', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com o PPP
            $table->foreignId('ppp_id')
                  ->constrained('pca_ppps')
                  ->onDelete('cascade')
                  ->comment('ID do PPP relacionado');
            
            // Status anterior (nullable para o primeiro registro)
            $table->foreignId('status_anterior')
                  ->nullable()
                  ->constrained('ppp_statuses')
                  ->comment('Status anterior do PPP');
            
            // Status atual
            $table->foreignId('status_atual')
                  ->constrained('ppp_statuses')
                  ->comment('Status atual do PPP');
            
            // Relacionamento com status dinâmico (NOVO CAMPO)
            $table->foreignId('status_dinamico_id')
                  ->nullable()
                  ->constrained('ppp_status_dinamicos')
                  ->onDelete('set null')
                  ->comment('ID do status dinâmico relacionado (se aplicável)');
            
            // Justificativa/comentário da mudança
            $table->text('justificativa')
                  ->nullable()
                  ->comment('Justificativa ou comentário da mudança de status');
            
            // Usuário responsável pela ação
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->comment('Usuário que realizou a ação');
            
            // Tipo de ação realizada (ENUM EXPANDIDO)
            $table->enum('acao', [
                'criacao',
                'edicao', 
                'aprovacao',
                'solicitacao_correcao',
                'reprovacao',
                'exclusao',
                'envio_aprovacao',
                'inicio_avaliacao',
                'inicio_correcao',
                'envio_correcao',
                'cancelamento'
            ])->comment('Tipo de ação realizada no PPP');
            
            // Dados adicionais em JSON (opcional)
            $table->json('dados_adicionais')
                  ->nullable()
                  ->comment('Dados adicionais da ação em formato JSON');
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['ppp_id', 'created_at'], 'idx_ppp_historicos_ppp_data');
            $table->index(['user_id', 'created_at'], 'idx_ppp_historicos_user_data');
            $table->index(['status_atual', 'created_at'], 'idx_ppp_historicos_status_data');
            $table->index('acao', 'idx_ppp_historicos_acao');
            $table->index('status_dinamico_id', 'idx_ppp_historicos_status_dinamico');
            $table->index(['ppp_id', 'acao'], 'idx_ppp_historicos_ppp_acao');
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppp_historicos');
    }
};
