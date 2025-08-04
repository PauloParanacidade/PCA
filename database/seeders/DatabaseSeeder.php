<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,          // 1º - Criar roles primeiro
            PPPStatusSeeder::class,     // 2º - Criar status
            GestorRoleSeeder::class,    // 3º - Atribuir roles de gestor
            UserRoleSeeder::class,      // 4º - Atribuir roles específicas
        ]);
    }
}
