<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('account_id')->constrained('accounts'); // Chave estrangeira para a conta
            $table->decimal('amount', 10, 2); // Valor do saque
            $table->foreignId('pix_key_id')->constrained('pix_keys'); // Chave estrangeira para a chave PIX
            $table->string('status', 20)->default('pending'); // Status: pending, scheduled, completed, failed
            $table->timestamp('scheduled_for')->nullable();
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
