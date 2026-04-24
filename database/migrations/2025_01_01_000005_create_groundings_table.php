<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('groundings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('exercise_type');
            $table->integer('duration')->nullable();
            $table->integer('calm_level_before')->nullable();
            $table->integer('calm_level_after')->nullable();
            $table->text('notes')->nullable();
            $table->integer('completed_steps')->default(0);
            $table->integer('total_steps')->default(5);
            $table->boolean('is_completed')->default(false);
            $table->json('progress')->nullable();
            $table->json('completed_steps_json')->nullable();
            $table->date('date');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('groundings');
    }
};