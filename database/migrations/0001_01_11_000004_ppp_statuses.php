<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppp_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // ex: 'enviou_para_avaliacao', 'esta_avaliando', 'aprovado_enviado', 'cancelado'
            $table->text('template'); // ex: '[remetente] Enviou para [destinatario] para avaliação'
            $table->string('slug')->unique();
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppp_statuses');
    }
};


## Status completos que precisamos:

// 1. **enviou_para_avaliacao** - "[remetente] Enviou para [destinatario] para avaliação"
// 2. **esta_avaliando** - "[destinatario] está avaliando a PPP"
// 3. **solicitou_correcao** - "[remetente] solicitou correção para [destinatario]"
// 4. **esta_corrigindo** - "[destinatario] está corrigindo a PPP"
// 5. **enviou_correcao** - "[remetente] Enviou correção para [destinatario]"
// 6. **aprovado_enviado** - "Aprovado pelo [remetente] e enviado para [destinatario] para avaliação"
// 7. **cancelado** - "Cancelado pelo [remetente]"

## Migration corrigida: