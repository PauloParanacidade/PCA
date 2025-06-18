<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pca_ppps', function (Blueprint $table) {
            $table->id(); // equivale ao INT NOT NULL AUTO_INCREMENT com chave primária

            $table->string('area_solicitante', 45);
            $table->string('area_responsavel', 45);

            $table->date('data_status',);

            $table->unsignedInteger('cod_id_item')->nullable();
            $table->string('categoria', 45);
            $table->string('nome_item', 100);
            $table->string('descricao', 100);
            $table->string('quantidade', 45);
            $table->string('justificativa_pedido', 100);

            $table->unsignedInteger('estimativa_valor');
            $table->string('justificativa_valor', 45);
            $table->string('origem_recurso', 20);
            $table->string('grau_prioridade', 20);

            $table->string('ate_partir_dia', 20); // 'Até', 'A partir de', 'No dia'
            $table->date('data_ideal_aquisicao');

            $table->boolean('vinculacao_item');
            $table->string('justificativa_vinculacao', 100)->nullable();

            $table->boolean('renov_contrato');
            $table->unsignedInteger('num_contrato')->nullable();
            $table->unsignedInteger('valor_contrato_atualizado')->nullable();

            $table->string('historico',256)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pca_ppps');
    }
};