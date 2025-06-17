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
            $table->string('firstname')->nullable()->after('name');
            $table->string('lastname')->nullable()->after('firstname');
            $table->string('position')->nullable()->after('lastname');
            $table->text('about')->nullable()->after('position');
            $table->json('social_media')->nullable()->after('about');
            $table->integer('total_tickets')->default(0)->after('social_media');
            $table->integer('remaining_tickets')->default(0)->after('total_tickets');
            $table->enum('role', ['user', 'admin', 'god'])->default('user')->after('remaining_tickets');
            $table->json('displayed_badges')->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'firstname',
                'lastname',
                'position',
                'about',
                'social_media',
                'total_tickets',
                'remaining_tickets',
                'role',
                'displayed_badges'
            ]);
        });
    }
};
