<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RemoveAdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        if (!$adminRole) {
            $this->command->error('❌ Role "admin" não encontrada. Execute RoleSeeder primeiro.');
            return;
        }

        // Lista dos nomes dos usuários que devem perder a role admin
        $usersToRemoveAdmin = [
            'Camila Mileke Scucato',
            'Anibal Andre Antunes Mendes',
            'Maximiliano William Alves',
            'Aluisio Clementino Soares',
            'Thais Fernanda Ortega Santos',
            'Francisco Luís dos Santos',
            'Mario Luis Braz Junior',
            // adicione mais nomes se precisar remover de outros usuários
        ];

        foreach ($usersToRemoveAdmin as $name) {
            $user = User::where('name', $name)->first();
            if ($user) {
                $user->roles()->detach($adminRole->id);
                $this->command->info("❌ Role admin removida de {$name} (ID {$user->id})");
            } else {
                $this->command->warn("⚠️ Usuário {$name} não encontrado.");
            }
        }
    }
}
