<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });

        // Generate UUIDs for existing images
        DB::table('images')->whereNull('uuid')->get()->each(function ($image) {
            DB::table('images')->where('id', $image->id)->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table('images', function (Blueprint $table) {
            $table->unique('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
