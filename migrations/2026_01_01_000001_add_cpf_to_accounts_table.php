<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddCpfToAccountsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Adiciona a coluna cpf após a coluna 'name'
            // Garante que seja única e não nula.
            $table->string('cpf', 11)->unique()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Remove a coluna e o índice de unicidade
            $table->dropUnique(['cpf']);
            $table->dropColumn('cpf');
        });
    }
}