<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pca_ppps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('area_solicitante', 45);
            $table->string('area_responsavel', 45);

            $table->unsignedInteger('cod_id_item')->nullable();
            $table->string('categoria', 45);
            $table->string('nome_item', 100);
            $table->string('descricao', 255); // mais espaço para texto
            $table->string('quantidade', 45);
            $table->string('justificativa_pedido', 100);

            $table->decimal('estimativa_valor', 10, 2);
            $table->string('origem_recurso', 20);
            $table->string('justificativa_valor', 100);
            $table->string('grau_prioridade', 20);

            $table->string('ate_partir_dia', 20);
            $table->date('data_ideal_aquisicao');

            $table->enum('vinculacao_item', ['Sim', 'Não']);
            $table->string('justificativa_vinculacao', 100)->nullable();

            $table->enum('renov_contrato', ['Sim', 'Não']);
            $table->date('previsao')->nullable();
            $table->string('num_contrato', 10)->nullable();
            $table->decimal('valor_contrato_atualizado', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pca_ppps');
    }
};
