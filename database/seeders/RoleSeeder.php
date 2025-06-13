<?php
namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'admin', 'description' => 'Administrador do Sistema']);
        Role::create(['name' => 'manager', 'description' => 'Gerente']);
        Role::create(['name' => 'external', 'description' => 'Usuário Externo']);
        Role::create(['name' => 'user', 'description' => 'Usuário Padrão']);
    }
}
