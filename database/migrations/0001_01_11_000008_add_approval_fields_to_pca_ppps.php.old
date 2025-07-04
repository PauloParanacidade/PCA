<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pca_ppps', function (Blueprint $table) {
            $table->foreignId('gestor_atual_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status_fluxo', [
                'rascunho',
                'aguardando_aprovacao', 
                'em_avaliacao',
                'em_correcao',
                'aprovado_final',
                'cancelado'
            ])->default('rascunho');
            
            $table->index(['gestor_atual_id', 'status_fluxo']);
        });
    }

    public function down(): void
    {
        Schema::table('pca_ppps', function (Blueprint $table) {
            $table->dropForeign(['gestor_atual_id']);
            $table->dropColumn(['gestor_atual_id', 'status_fluxo']);
        });
    }
};