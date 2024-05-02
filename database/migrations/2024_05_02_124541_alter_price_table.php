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
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropColumn('hourly_price_ht');
        });

        Schema::table('formations', function (Blueprint $table) {
            $table->float('hourly_price_ht')->after('aleas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->decimal('hourly_price_ht', 15, 2)->nullable();
        });

        Schema::table('formations', function (Blueprint $table) {
            $table->dropColumn('hourly_price_ht');
        });
    }
};
