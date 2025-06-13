<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabelas auxiliares que devem existir antes (referência de FK)
        Schema::create('PCA_contrato', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 45);
            $table->integer('valor');
            $table->integer('aditivo');
            $table->unique('id');
        });

        Schema::create('PCA_nome_item', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 45);
            $table->unique('id');
        });

        Schema::create('PCA_categoria', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 45);
            $table->unique('id');
        });

        // Tabela principal com FK e autorreferência
        Schema::create('PCA_solicitacao', function (Blueprint $table) {
            $table->increments('id');
            $table->string('area_solicitante', 45);
            $table->string('area_responsavel', 45);
            $table->integer('id_item');
            $table->unsignedInteger('PCA_categoria_id');
            $table->unsignedInteger('PCA_nome_item_id');
            $table->string('descricao', 100);
            $table->string('quantidade', 45);
            $table->string('justificativa_pedido', 45);
            $table->integer('estimativa_valor');
            $table->string('justificativa_valor', 45);
            $table->string('origem_recurso', 45);
            $table->string('grau_prioridade', 45);
            $table->string('data_ideal_aquisicao', 45);
            $table->boolean('vinculacao_item');
            $table->unsignedInteger('PCA_solicitacao_id')->nullable();// na label ficará "item vinculado"
            $table->string('justificativa_vinculacao', 100)->nullable();
            $table->dateTime('dt_preenchimento');
            $table->unsignedInteger('PCA_contrato_id');

            // Indexes
            $table->index('PCA_categoria_id', 'fk_PCA_solicitacao_PCA_categoria1_idx');
            $table->index('PCA_nome_item_id', 'fk_PCA_solicitacao_PCA_nome_item1_idx');
            $table->index('PCA_contrato_id', 'fk_PCA_solicitacao_PCA_contrato1_idx');
            $table->index('PCA_solicitacao_id', 'fk_PCA_solicitacao_PCA_solicitacao1_idx');// na label ficará "item vinculado"

            // Foreign Keys
            $table->foreign('PCA_contrato_id')
                ->references('id')
                ->on('PCA_contrato')
                ->onUpdate('no action')
                ->onDelete('no action');

            $table->foreign('PCA_nome_item_id')
                ->references('id')
                ->on('PCA_nome_item')
                ->onUpdate('no action')
                ->onDelete('no action');

            $table->foreign('PCA_categoria_id')
                ->references('id')
                ->on('PCA_categoria')
                ->onUpdate('no action')
                ->onDelete('no action');

            $table->foreign('PCA_solicitacao_id')
                ->references('id')
                ->on('PCA_solicitacao')
                ->onUpdate('no action')
                ->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PCA_solicitacao');
        Schema::dropIfExists('PCA_categoria');
        Schema::dropIfExists('PCA_nome_item');
        Schema::dropIfExists('PCA_contrato');
    }
};
