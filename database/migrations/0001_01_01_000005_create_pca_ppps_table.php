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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Usuário criador do PPP');
            $table->foreignId('status_id')->default(1)->constrained('ppp_statuses')->comment('Status atual do PPP');
            
            // Campos de Fluxo de Aprovação
            $table->foreignId('gestor_atual_id')->nullable()->constrained('users')->onDelete('set null')->comment('Gestor responsável pela aprovação atual');
            $table->enum('status_fluxo', [
                'novo',
                'aguardando_aprovacao', 
                'em_avaliacao',
                'em_correcao',
                'aprovado_final',
                'cancelado'
            ])->default('novo')->comment('Status do fluxo de aprovação');
            $table->timestamp('data_ultima_aprovacao')->nullable()->comment('Data da última ação de aprovação');

            // Detalhes do Item
            $table->string('categoria', 100)->comment('Categoria do item/serviço');
            $table->string('nome_item', 200)->comment('Nome/título do item');
            $table->text('descricao')->comment('Descrição detalhada do item');
            $table->string('quantidade', 50)->comment('Quantidade solicitada');
            $table->text('justificativa_pedido')->comment('Justificativa para o pedido');

            // Informações Financeiras
            $table->decimal('estimativa_valor', 12, 2)->comment('Valor estimado da aquisição');
            $table->string('origem_recurso', 100)->comment('Origem do recurso financeiro');
            $table->text('justificativa_valor')->comment('Justificativa do valor estimado');
            $table->enum('grau_prioridade', ['Baixa', 'Média', 'Alta', 'Urgente'])->default('Média')->comment('Grau de prioridade');

            // Cronograma
            $table->string('ate_partir_dia', 50)->comment('A partir de quando pode ser adquirido');
            $table->date('data_ideal_aquisicao')->comment('Data ideal para aquisição');

            // Vinculação
            $table->boolean('vinculacao_item')->default(false)->comment('Item possui vinculação');
            $table->text('justificativa_vinculacao')->nullable()->comment('Justificativa da vinculação');

            // Renovação de Contrato
            $table->boolean('renov_contrato')->default(false)->comment('É renovação de contrato');
            $table->date('previsao')->nullable()->comment('Previsão de renovação');
            $table->string('num_contrato', 50)->nullable()->comment('Número do contrato atual');
            $table->decimal('valor_contrato_atualizado', 12, 2)->nullable()->comment('Valor atualizado do contrato');

            // Soft Delete
            $table->softDeletes();
            $table->timestamps();

            // Índices para performance
            $table->index(['user_id', 'status_id']);
            $table->index(['gestor_atual_id', 'status_fluxo']); // Novo índice
            $table->index(['categoria', 'grau_prioridade']);
            $table->index('data_ideal_aquisicao');
            $table->index('deleted_at');
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