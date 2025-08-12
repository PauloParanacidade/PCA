<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Inserir a role CLC
        DB::table('roles')->insert([
            'name' => 'clc',
            'description' => 'CLC - Coordenador de Licitações e Contratos',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover a role CLC
        DB::table('roles')->where('name', 'clc')->delete();
    }
};
