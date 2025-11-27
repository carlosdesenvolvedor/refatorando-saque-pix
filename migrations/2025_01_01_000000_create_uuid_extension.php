<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;
use Hyperf\DbConnection\Db;

class CreateUuidExtension extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Db::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Db::statement('DROP EXTENSION IF EXISTS "uuid-ossp";');
    }
}
