<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('breathing_sessions', function (Blueprint $table) {
            $table->integer('cycles')->default(0)->after('duration');
        });
    }

    public function down()
    {
        Schema::table('breathing_sessions', function (Blueprint $table) {
            $table->dropColumn('cycles');
        });
    }
};