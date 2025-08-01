<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pca_ppps', function (Blueprint $table) {
            $table->id();
            
            // === CONTROLE DE USUÁRIO E STATUS ===
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->comment('Usuário criador do PPP');
                  
            $table->foreignId('status_id')
                  ->default(1)
                  ->constrained('ppp_statuses')
                  ->comment('Status atual do PPP (referência para tabela simplificada)');
            
            // === CONTROLE DE FLUXO DE APROVAÇÃO ===
            $table->foreignId('gestor_atual_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('Gestor responsável pela aprovação atual');
                  
            $table->timestamp('data_ultima_aprovacao')
                  ->nullable()
                  ->comment('Data da última ação de aprovação');
            
            // === CARD AZUL - DETALHES DO ITEM/SERVIÇO ===
            $table->string('nome_item', 200)
                  ->comment('Nome/título do item');
                  
            $table->string('quantidade', 50)
                  ->comment('Quantidade solicitada');
            
            $table->enum('grau_prioridade', ['Baixa', 'Média', 'Alta', 'Urgente'])
                  ->default('Média')
                  ->comment('Grau de prioridade');

            $table->text('descricao')
                  ->comment('Descrição detalhada do item');
                  
            $table->string('natureza_objeto', 100)
                  ->comment('Natureza do objeto (bem/serviço)');

            $table->text('justificativa_pedido')
                  ->comment('Justificativa para o pedido');

            $table->string('categoria', 100)
                  ->comment('Categoria do item/serviço');

            // === CARD AMARELO - CONTRATO VIGENTE ===
            $table->enum('tem_contrato_vigente', ['Sim', 'Não'])
                  ->comment('Possui contrato vigente');

            $table->string('mes_inicio_prestacao', 10)
                  ->nullable()
                  ->comment('Mês de início da prestação do serviço quando não tiver contrato vigente');
            
            $table->year('ano_pca')
                  ->nullable()
                  ->comment('Ano do PCA (preenchido automaticamente como ano atual + 1) para objeto novo');

            $table->enum('contrato_mais_um_exercicio', ['Sim', 'Não'])
                  ->nullable()
                  ->comment('O contrato é mais de um exercício');
                  
            $table->unsignedSmallInteger('num_contrato')
                  ->nullable()
                  ->comment('Número do contrato atual (1-9999) - obrigatório se tem_contrato_vigente = Sim');
                  
            $table->year('ano_contrato')
                  ->nullable()
                  ->comment('Ano do contrato atual (obrigatório se tem_contrato_vigente = Sim)');
                  
            $table->string('mes_vigencia_final', 10)
                  ->nullable()
                  ->comment('Mês de vigência final do contrato');
                  
            $table->year('ano_vigencia_final')
                  ->nullable()
                  ->comment('Ano de vigência final do contrato para comparação com PCA');
                  
            $table->enum('contrato_prorrogavel', ['Sim', 'Não'])
                  ->nullable()
                  ->comment('Contrato é prorrogável (obrigatório se tem_contrato_vigente = Sim)');
                  
            $table->enum('renov_contrato', ['Sim', 'Não'])
                  ->nullable()
                  ->comment('Pretensão de prorrogação (obrigatório se tem_contrato_vigente = Sim)');
            
            // === CARD VERDE - INFORMAÇÕES FINANCEIRAS ===
            $table->decimal('estimativa_valor', 12, 2)
                  ->comment('Valor estimado da aquisição');
                  
            $table->string('origem_recurso', 100)
                  ->comment('Origem do recurso financeiro');
                  
            $table->text('justificativa_valor')
                  ->comment('Justificativa do valor estimado');
                  
            $table->decimal('valor_contrato_atualizado', 12, 2)
                  ->nullable()
                  ->comment('Valor atualizado do contrato (se aplicável)');
            
            // === CARD CIANO - VINCULAÇÃO/DEPENDÊNCIA ===
            $table->enum('vinculacao_item', ['Sim', 'Não'])
                  ->default('Não')
                  ->comment('Item possui vinculação/dependência');
                  
            $table->text('justificativa_vinculacao')
                  ->nullable()
                  ->comment('Justificativa da vinculação (obrigatória se vinculacao_item = Sim)');
            
            // === CONTROLE DO SISTEMA ===
            $table->softDeletes();
            $table->timestamps();
            
            // === ÍNDICES PARA PERFORMANCE ===
            $table->index(['user_id', 'status_id'], 'idx_pca_ppps_user_status');
            $table->index(['gestor_atual_id', 'status_id'], 'idx_pca_ppps_gestor_status');
            $table->index(['categoria', 'grau_prioridade'], 'idx_pca_ppps_categoria_prioridade');
            $table->index('deleted_at', 'idx_pca_ppps_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pca_ppps');
    }
};