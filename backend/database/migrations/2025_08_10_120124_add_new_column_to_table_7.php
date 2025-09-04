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
        //Thêm column
        Schema::table('project_provinces', function(Blueprint $table){

            $table->decimal('price_non', 15, 2)->after('price_boosters_5')->nullable();
            $table->decimal('price_non_1', 15, 2)->after('price_non')->nullable();
            $table->decimal('price_non_2', 15, 2)->after('price_non_1')->nullable();
            $table->decimal('price_non_3', 15, 2)->after('price_non_2')->nullable();
            $table->decimal('price_non_4', 15, 2)->after('price_non_3')->nullable();
            $table->decimal('price_non_5', 15, 2)->after('price_non_4')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //Xoá column 
        Schema::table('project_provinces', function (Blueprint $table) {
            $table->dropColumn('price_non');
            $table->dropColumn('price_non_1');
            $table->dropColumn('price_non_2');
            $table->dropColumn('price_non_3');
            $table->dropColumn('price_non_4');
            $table->dropColumn('price_non_5');
        });
    }
};
