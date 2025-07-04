<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('name', 'Paulo Rogério de Souza Filho')->first();

        if ($user) {
            DB::table('role_user')->insert([
                'role_id' => 1, // admin
                'user_id' => $user->id,
            ]);

            $this->command->info("Admin role atribuída ao usuário Paulo (ID {$user->id})");
        } else {
            $this->command->warn("Usuário 'Paulo' não encontrado.");
        }
    }
}
