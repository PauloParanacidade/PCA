<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Índice para otimizar consultas por manager (hierarquia)
            $table->index('manager', 'idx_users_manager');
            
            // Índice para otimizar consultas por department
            $table->index('department', 'idx_users_department');
            
            // Índice composto para consultas que filtram por active + manager
            $table->index(['active', 'manager'], 'idx_users_active_manager');
            
            // Índice composto para consultas que filtram por active + department
            $table->index(['active', 'department'], 'idx_users_active_department');
            
            // Índice composto para otimizar consultas hierárquicas completas
            $table->index(['active', 'manager', 'department'], 'idx_users_hierarchy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remover índices na ordem inversa
            $table->dropIndex('idx_users_hierarchy');
            $table->dropIndex('idx_users_active_department');
            $table->dropIndex('idx_users_active_manager');
            $table->dropIndex('idx_users_department');
            $table->dropIndex('idx_users_manager');
        });
    }
};
