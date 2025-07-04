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
        // Buscar role admin
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $this->command->error('❌ Role admin não encontrada. Execute RoleSeeder primeiro.');
            return;
        }

        // Tentar encontrar usuário específico
        $user = User::where('name', 'Paulo Rogério de Souza Filho')->first();
        
        if ($user) {
            // Atribuir role usando relacionamento Eloquent
            $user->roles()->syncWithoutDetaching([$adminRole->id]);
            $this->command->info("✅ Role admin atribuída ao usuário Paulo (ID {$user->id})");
        } else {
            // Fallback: criar usuário admin padrão
            $adminUser = User::firstOrCreate(
                ['email' => 'admin@paranacidade.org.br'],
                [
                    'name' => 'Administrador do Sistema',
                    'password' => Hash::make('admin123'),
                    'email_verified_at' => now(),
                ]
            );
            
            // Atribuir role admin
            $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
            
            $this->command->info("✅ Usuário admin padrão criado e role atribuída (ID {$adminUser->id})");
            $this->command->warn('⚠️  Senha padrão: admin123 - ALTERE após primeiro login!');
        }
    }
}