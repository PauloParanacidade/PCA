<?php

namespace Database\Seeders;

use App\Models\PppStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PPPStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'nome' => 'Rascunho',
                'slug' => 'rascunho',
                'descricao' => 'PPP em elaboração pelo usuário',
                'ordem' => 1,
                'ativo' => true,
                'cor' => '#6c757d', // cinza
            ],
            [
                'nome' => 'Aguardando Aprovação',
                'slug' => 'aguardando_aprovacao',
                'descricao' => 'PPP enviado e aguardando aprovação do gestor',
                'ordem' => 2,
                'ativo' => true,
                'cor' => '#17a2b8', // azul
            ],
            [
                'nome' => 'Em Avaliação',
                'slug' => 'em_avaliacao',
                'descricao' => 'PPP sendo avaliado pelo gestor responsável',
                'ordem' => 3,
                'ativo' => true,
                'cor' => '#ffc107', // amarelo
            ],
            [
                'nome' => 'Aguardando Correção',
                'slug' => 'aguardando_correcao',
                'descricao' => 'PPP retornado para correções solicitadas',
                'ordem' => 4,
                'ativo' => true,
                'cor' => '#fd7e14', // laranja
            ],
            [
                'nome' => 'Em Correção',
                'slug' => 'em_correcao',
                'descricao' => 'PPP sendo corrigido pelo usuário',
                'ordem' => 5,
                'ativo' => true,
                'cor' => '#6f42c1', // roxo
            ],
            [
                'nome' => 'Cancelado',
                'slug' => 'cancelado',
                'descricao' => 'PPP cancelado ou reprovado',
                'ordem' => 6,
                'ativo' => true,
                'cor' => '#dc3545', // vermelho
            ],
            [
                'nome' => 'Aguardando DIREX',
                'slug' => 'aguardando_direx',
                'descricao' => 'PPP aguardando avaliação da DIREX',
                'ordem' => 7,
                'ativo' => true,
                'cor' => '#20c997', // verde-azulado
            ],
            [
                'nome' => 'DIREX Avaliando',
                'slug' => 'direx_avaliando',
                'descricao' => 'PPP sendo avaliado na reunião da DIREX',
                'ordem' => 8,
                'ativo' => true,
                'cor' => '#007bff', // azul primário
            ],
            [
                'nome' => 'DIREX Editado',
                'slug' => 'direx_editado',
                'descricao' => 'PPP editado durante reunião da DIREX',
                'ordem' => 9,
                'ativo' => true,
                'cor' => '#17a2b8', // azul claro
            ],
            [
                'nome' => 'Aguardando Conselho',
                'slug' => 'aguardando_conselho',
                'descricao' => 'PPP aguardando aprovação do Conselho',
                'ordem' => 10,
                'ativo' => true,
                'cor' => '#6610f2', // índigo
            ],
            [
                'nome' => 'Conselho Aprovado',
                'slug' => 'conselho_aprovado',
                'descricao' => 'PPP aprovado pelo Conselho',
                'ordem' => 11,
                'ativo' => true,
                'cor' => '#6f42c1', // roxo
            ],
            [
                'nome' => 'Conselho Reprovado',
                'slug' => 'conselho_reprovado',
                'descricao' => 'PPP reprovado pelo Conselho',
                'ordem' => 12,
                'ativo' => true,
                'cor' => '#e83e8c', // rosa
            ],
        ];

        foreach ($statuses as $status) {
            PppStatus::firstOrCreate(
                ['slug' => $status['slug']], // critério de busca
                $status // dados para criação
            );
            
            Log::info("Status '{$status['nome']}' processado com sucesso.");
        }
        
        $this->command->info('✅ PPP Status seeders executados com sucesso!');
    }
}
