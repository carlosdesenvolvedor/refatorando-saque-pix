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
        Schema::table('pix_keys', function (Blueprint $table) {
            $table->foreignId('account_id')->after('key')->constrained('accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pix_keys', function (Blueprint $table) {
            
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
