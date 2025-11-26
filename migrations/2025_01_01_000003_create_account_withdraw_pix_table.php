<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateAccountWithdrawPixTable extends Migration
{
    public function up(): void
    {
        Schema::create('account_withdraw_pix', function (Blueprint $table) {
            $table->foreignUuid('account_withdraw_id')->primary()->constrained('account_withdraws')->onDelete('cascade');
            $table->string('type');
            $table->string('key');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_withdraw_pix');
    }
}