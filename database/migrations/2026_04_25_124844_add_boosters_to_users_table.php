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
            $table->json('moodlifting')->nullable();
            $table->json('mindreset')->nullable();
            $table->json('minitask')->nullable();
            $table->json('bodybooster')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['moodlifting', 'mindreset', 'minitask', 'bodybooster']);
        });
    }
};
