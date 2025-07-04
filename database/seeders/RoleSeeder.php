<?php
namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrador do Sistema'
            ],
            [
                'name' => 'daf',
                'description' => 'DAF - Diretoria Administrativa e Financeira'
            ],
            [
                'name' => 'gestor',
                'description' => 'Gestor - Avaliador de PPPs'
            ],
            [
                'name' => 'user',
                'description' => 'Usuário Padrão - Solicitante de PPPs'
            ],
            [
                'name' => 'external',
                'description' => 'Usuário Externo'
            ]
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }

        $this->command->info('Roles criadas com sucesso!');
    }
}
