<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Busca das roles
        $adminRole = Role::where('name', 'admin')->first();
        $secretariaRole = Role::where('name', 'secretaria')->first();

        if (!$adminRole || !$secretariaRole) {
            $this->command->error('❌ Role admin ou secretaria não encontrada. Execute RoleSeeder primeiro.');
            return;
        }

        // Lista de admins
        $adminUsers = [
            'Paulo Rogério de Souza Filho',
            // 'Camila Mileke Scucato',
            // 'Anibal Andre Antunes Mendes',
            // 'Maximiliano William Alves',
            'Ramon Kowalski Jordão',
            // 'Aluisio Clementino Soares',
            // 'Thais Fernanda Ortega Santos',
            // 'Francisco Luís dos Santos',
            // 'Mario Luis Braz Junior'
        ];

        foreach ($adminUsers as $name) {
            $user = User::where('name', $name)->first();

            if ($user) {
                $user->roles()->syncWithoutDetaching([$adminRole->id]);
                $this->command->info("✅ Role admin atribuída a {$name} (ID {$user->id})");
            } else {
                $this->command->warn("⚠️  Usuário {$name} não encontrado.");
            }
        }

        // Atribuir role secretaria à Vera
        $vera = User::where('name', 'Vera Morais Ferreira')->first();
        if ($vera) {
            $vera->roles()->syncWithoutDetaching([$secretariaRole->id]);
            $this->command->info("✅ Role secretaria atribuída à Vera Morais Ferreira (ID {$vera->id})");
        } else {
            $this->command->warn('⚠️  Usuária Vera Morais Ferreira não encontrada.');
        }

        // Fallback para admin padrão, se quiser manter
        $fallbackUser = User::firstOrCreate(
            ['email' => 'admin@paranacidade.org.br'],
            [
                'name' => 'Administrador do Sistema',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );
        $fallbackUser->roles()->syncWithoutDetaching([$adminRole->id]);
        $this->command->info("✅ Usuário admin padrão garantido (ID {$fallbackUser->id})");
    }
}
