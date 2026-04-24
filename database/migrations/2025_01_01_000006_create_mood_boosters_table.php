<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mood_boosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('booster_type'); // bodybooster, minitask, mindreset, moodlifting
            $table->string('activity_name')->nullable();
            $table->integer('duration')->default(0);
            $table->integer('mood_before')->nullable();
            $table->integer('mood_after')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'booster_type']);
            $table->index(['user_id', 'completed_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mood_boosters');
    }
};