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
        DB::statement("ALTER TABLE events MODIFY COLUMN status ENUM('draft', 'published', 'cancelled', 'completed', 'active', 'deleted') NOT NULL DEFAULT 'draft'");

        DB::update("UPDATE events SET status = 'active' WHERE status IN ('draft', 'published')");
        DB::update("UPDATE events SET status = 'deleted' WHERE status IN ('cancelled', 'completed')");

        DB::statement("ALTER TABLE events MODIFY COLUMN status ENUM('active', 'deleted') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE events MODIFY COLUMN status ENUM('draft', 'published', 'cancelled', 'completed') NOT NULL DEFAULT 'draft'");

        DB::update("UPDATE events SET status = 'published' WHERE status = 'active'");
        DB::update("UPDATE events SET status = 'cancelled' WHERE status = 'deleted'");
    }
};
