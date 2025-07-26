<?php

use App\Models\Crudkegiatan;
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
        Schema::table('Crudkegiatan', function (Blueprint $table) {
            $table->string('slug_lama')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crudkegiatan', function (Blueprint $table) {
            //
        });
    }
};
