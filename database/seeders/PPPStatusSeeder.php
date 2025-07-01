<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PPPStatusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ppp_statuses')->insert([
            [
                'id' => 1,
                'tipo' => 'enviou_para_avaliacao',
                'template' => '[remetente] Enviou para [destinatario] para avaliação',
                'slug' => 'enviou_para_avaliacao',
                'ordem' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'tipo' => 'esta_avaliando',
                'template' => '[destinatario] está avaliando a PPP',
                'slug' => 'esta_avaliando',
                'ordem' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'tipo' => 'solicitou_correcao',
                'template' => '[remetente] solicitou correção para [destinatario]',
                'slug' => 'solicitou_correcao',
                'ordem' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'tipo' => 'esta_corrigindo',
                'template' => '[destinatario] está corrigindo a PPP',
                'slug' => 'esta_corrigindo',
                'ordem' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'tipo' => 'enviou_correcao',
                'template' => '[remetente] Enviou correção para [destinatario]',
                'slug' => 'enviou_correcao',
                'ordem' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'tipo' => 'aprovado_enviado',
                'template' => 'Aprovado pelo [remetente] e enviado para [destinatario] para avaliação',
                'slug' => 'aprovado_enviado',
                'ordem' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'tipo' => 'cancelado',
                'template' => 'Cancelado pelo [remetente]',
                'slug' => 'cancelado',
                'ordem' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
