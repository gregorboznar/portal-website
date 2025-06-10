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
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false)->after('views_count');
            $table->timestamp('pinned_at')->nullable()->after('is_pinned');

            $table->index('is_pinned');
            $table->index(['is_pinned', 'pinned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['posts_is_pinned_index']);
            $table->dropIndex(['posts_is_pinned_pinned_at_index']);
            $table->dropColumn(['is_pinned', 'pinned_at']);
        });
    }
};
