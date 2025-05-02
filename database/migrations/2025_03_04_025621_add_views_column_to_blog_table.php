<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddViewsColumnToBlogTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('blog', function (Blueprint $table) {
            $table->unsignedBigInteger('views')->default(0); // Menambahkan kolom `views` dengan nilai default 0
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('blog', function (Blueprint $table) {
            $table->dropColumn('views'); // Menghapus kolom `views` jika migrasi di-rollback
        });
    }
}