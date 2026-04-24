<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mind_resets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('technique');
            $table->integer('duration')->default(0);
            $table->integer('stress_before')->nullable();
            $table->integer('stress_after')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'completed']);
            $table->index(['user_id', 'completed_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mind_resets');
    }
};