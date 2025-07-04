<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppp_status_dinamicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppp_id')->constrained('pca_ppps')->onDelete('cascade');
            $table->foreignId('status_tipo_id')->constrained('ppp_statuses')->onDelete('cascade');
            $table->string('remetente_nome')->nullable();
            $table->string('remetente_sigla')->nullable();
            $table->string('destinatario_nome')->nullable();
            $table->string('destinatario_sigla')->nullable();
            $table->text('status_formatado'); // Status final formatado
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            $table->index(['ppp_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppp_status_dinamicos');
    }
};