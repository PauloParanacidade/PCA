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
        Schema::create('ppp_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 50)->comment('Nome do status');
            $table->string('slug', 50)->unique()->comment('Identificador único para o status');
            $table->text('descricao')->nullable()->comment('Descrição detalhada do status');
            $table->integer('ordem')->comment('Ordem de exibição/processamento');
            $table->boolean('ativo')->default(true)->comment('Status ativo no sistema');
            $table->string('cor', 7)->default('#6c757d')->comment('Cor hexadecimal para exibição');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['ativo', 'ordem']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppp_statuses');
    }
};