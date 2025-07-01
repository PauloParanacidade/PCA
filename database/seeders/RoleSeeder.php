<?php
namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'admin', 'description' => 'Administrador do Sistema']);
        Role::create(['name' => 'daf', 'description' => 'DAF']); // DAF terá acesso CRUD a todos os PPPs da empresa
        Role::create(['name' => 'supImediato', 'description' => 'Superior Imediato']); // terá acesso CRUD a todos os PPPs de sua organização
        Role::create(['name' => 'coord', 'description' => 'Coordenador']); // terá acesso CRUD a todos os PPPs de sua área
        Role::create(['name' => 'external', 'description' => 'Usuário Externo']);
        Role::create(['name' => 'user', 'description' => 'Usuário Padrão']); // solicitante terá acesso CRUD apenas aos seus próprios PPPs
    }
}
