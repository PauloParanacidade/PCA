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
                'id'    => 1,
                'nome'  => 'Enviado para Aprovação',
                'slug'  => 'enviado_aprovacao',
                'ordem' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'    => 2,
                'nome'  => 'Em Análise',
                'slug'  => 'em_analise',
                'ordem' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'    => 3,
                'nome'  => 'Solicitou Correção',
                'slug'  => 'solicitou_correcao',
                'ordem' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'    => 4,
                'nome'  => 'Usuário Corrigindo',
                'slug'  => 'usuario_corrigindo',
                'ordem' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'    => 5,
                'nome'  => 'Correção Enviada',
                'slug'  => 'correcao_enviada',
                'ordem' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'    => 6,
                'nome'  => 'Aprovado',
                'slug'  => 'aprovado',
                'ordem' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'    => 7,
                'nome'  => 'Cancelado',
                'slug'  => 'cancelado',
                'ordem' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}