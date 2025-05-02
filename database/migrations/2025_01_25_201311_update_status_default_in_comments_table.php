<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('post_comments', function (Blueprint $table) {
        $table->enum('status', ['pending', 'setuju', 'tolak'])->default('pending')->change();
    });
}

public function down()
{
    Schema::table('post_comments', function (Blueprint $table) {
        $table->enum('status', ['setuju', 'tolak'])->default(null)->change();
    });
}


};
