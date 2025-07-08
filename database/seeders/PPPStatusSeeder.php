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
                'nome' => 'Aprovado Final',
                'slug' => 'aprovado_final',
                'descricao' => 'PPP aprovado em todos os níveis hierárquicos',
                'ordem' => 6,
                'ativo' => true,
                'cor' => '#28a745', // verde
            ],
            [
                'nome' => 'Cancelado',
                'slug' => 'cancelado',
                'descricao' => 'PPP cancelado ou reprovado',
                'ordem' => 7,
                'ativo' => true,
                'cor' => '#dc3545', // vermelho
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