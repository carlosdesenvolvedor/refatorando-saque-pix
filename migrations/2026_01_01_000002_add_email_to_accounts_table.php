<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class AddEmailToAccountsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Adiciona a coluna 'email' após a coluna 'balance'.
            // É nullable para não quebrar registros existentes.
            // É unique para garantir que cada conta tenha um e-mail exclusivo.
            $table->string('email')->nullable()->unique()->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Remove a chave única e a coluna para permitir o rollback.
            $table->dropUnique(['email']);
            $table->dropColumn('email');
        });
    }
}