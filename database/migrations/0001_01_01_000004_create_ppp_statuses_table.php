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
            $table->string('tipo', 50)->comment('Tipo do status: enviou_para_avaliacao, esta_avaliando, etc.');
            $table->text('template')->comment('Template da mensagem com placeholders [remetente] e [destinatario]');
            $table->string('slug', 50)->unique()->comment('Identificador único para o status');
            $table->integer('ordem')->default(0)->comment('Ordem de exibição/processamento');
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