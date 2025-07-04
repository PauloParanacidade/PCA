<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration para criar a tabela de status dinâmicos dos PPPs.
     * Armazena os status formatados dinamicamente com informações de remetente e destinatário.
     */
    public function up(): void
    {
        Schema::create('ppp_status_dinamicos', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com o PPP
            $table->foreignId('ppp_id')
                  ->constrained('pca_ppps')
                  ->onDelete('cascade')
                  ->comment('ID do PPP relacionado');
            
            // Tipo de status base
            $table->foreignId('status_tipo_id')
                  ->constrained('ppp_statuses')
                  ->onDelete('cascade')
                  ->comment('Tipo de status base utilizado');
            
            // Informações do remetente
            $table->string('remetente_nome', 100)
                  ->nullable()
                  ->comment('Nome do remetente/setor de origem');
            
            $table->string('remetente_sigla', 20)
                  ->nullable()
                  ->comment('Sigla do setor remetente');
            
            // Informações do destinatário
            $table->string('destinatario_nome', 100)
                  ->nullable()
                  ->comment('Nome do destinatário/setor de destino');
            
            $table->string('destinatario_sigla', 20)
                  ->nullable()
                  ->comment('Sigla do setor destinatário');
            
            // Status formatado final
            $table->text('status_formatado')
                  ->comment('Status final formatado dinamicamente');
            
            // Controle de ativação
            $table->boolean('ativo')
                  ->default(true)
                  ->comment('Indica se o status dinâmico está ativo');
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['ppp_id', 'ativo'], 'idx_ppp_status_dinamicos_ppp_ativo');
            $table->index(['status_tipo_id', 'ativo'], 'idx_ppp_status_dinamicos_tipo_ativo');
            $table->index('remetente_sigla', 'idx_ppp_status_dinamicos_remetente');
            $table->index('destinatario_sigla', 'idx_ppp_status_dinamicos_destinatario');
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppp_status_dinamicos');
    }
};