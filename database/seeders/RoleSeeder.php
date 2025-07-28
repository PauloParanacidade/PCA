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
                'name' => 'secretaria',
                'description' => 'Secretária da DIREX e Conselho'
            ],
            [
                'name' => 'daf',
                'description' => 'DAF - Diretoria Administrativa e Financeira'
            ],
            [
                'name' => 'gestor',
                'description' => 'Gestor - Avaliador de PPPs'
            ],
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
