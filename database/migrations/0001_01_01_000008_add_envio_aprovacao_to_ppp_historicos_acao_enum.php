<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar 'envio_aprovacao' ao ENUM existente
        DB::statement("ALTER TABLE ppp_historicos MODIFY COLUMN acao ENUM(
            'criacao',
            'edicao',
            'envio_aprovacao',
            'aprovacao',
            'solicitacao_correcao',
            'reprovacao',
            'exclusao',
            'cancelamento'
        ) COMMENT 'Tipo de ação realizada no PPP'");
    }

    public function down(): void
    {
        // Remover 'envio_aprovacao' do ENUM
        DB::statement("ALTER TABLE ppp_historicos MODIFY COLUMN acao ENUM(
            'criacao',
            'edicao',
            'aprovacao',
            'solicitacao_correcao',
            'reprovacao',
            'exclusao',
            'cancelamento'
        ) COMMENT 'Tipo de ação realizada no PPP'");
    }
};