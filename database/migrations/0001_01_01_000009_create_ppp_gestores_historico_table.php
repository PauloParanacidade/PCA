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
        Schema::create('ppp_gestores_historico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppp_id')->constrained('pca_ppps')->onDelete('cascade');
            $table->foreignId('gestor_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('data_envio')->nullable();
            $table->timestamp('data_acao')->nullable(); // quando aprovou/reprovou
            $table->enum('acao', ['enviado', 'aprovado', 'reprovado', 'solicitou_correcao'])->nullable();
            $table->timestamps();
            
            $table->index(['ppp_id', 'gestor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppp_gestores_historico');
    }
};
