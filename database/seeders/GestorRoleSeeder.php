<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Log;

class GestorRoleSeeder extends Seeder
{
    public function run()
    {
        $gestorRole = Role::where('name', 'gestor')->first();
        $dafRole = Role::where('name', 'daf')->first();
        
        if (!$gestorRole || !$dafRole) {
            Log::error('Roles gestor ou daf não encontradas');
            return;
        }

        // Buscar todos os managers únicos
        $managers = User::whereNotNull('manager')
            ->pluck('manager')
            ->unique()
            ->filter()
            ->map(function ($manager) {
                // Extrair nome após CN=
                if (preg_match('/CN=([^,]+)/', $manager, $matches)) {
                    return trim($matches[1]);
                }
                return null;
            })
            ->filter()
            ->unique();

        foreach ($managers as $managerName) {
            // Buscar usuário pelo nome
            $user = User::where('name', 'LIKE', "%{$managerName}%")
                ->orWhere('name', $managerName)
                ->first();
                
            if ($user) {
                // Atribuir role gestor se não tiver
                if (!$user->hasRole('gestor')) {
                    $user->roles()->attach($gestorRole->id);
                    Log::info("Role gestor atribuída para: {$user->name}");
                }
                
                // Se department contém DAF, atribuir role daf
                if ($user->department && 
                    (stripos($user->department, 'DAF') !== false || 
                     stripos($user->department, 'daf') !== false)) {
                    if (!$user->hasRole('daf')) {
                        $user->roles()->attach($dafRole->id);
                        Log::info("Role DAF atribuída para: {$user->name}");
                    }
                }
            } else {
                Log::warning("Gestor não encontrado no sistema: {$managerName}");
            }
        }
    }
}