<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppp_historicos', function (Blueprint $table) {
            $table->foreignId('status_dinamico_id')->nullable()->constrained('ppp_status_dinamicos')->onDelete('set null');
            $table->enum('acao', [
                'criacao',
                'envio_aprovacao',
                'inicio_avaliacao',
                'aprovacao',
                'solicitacao_correcao',
                'inicio_correcao',
                'envio_correcao',
                'cancelamento'
            ])->nullable();
            
            $table->index(['ppp_id', 'acao']);
        });
    }

    public function down(): void
    {
        Schema::table('ppp_historicos', function (Blueprint $table) {
            $table->dropForeign(['status_dinamico_id']);
            $table->dropColumn(['status_dinamico_id', 'acao']);
        });
    }
};