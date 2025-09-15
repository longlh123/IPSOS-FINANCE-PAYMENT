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
        Schema::table('provinces', function(Blueprint $table){

            $table->string('codename')->after('area_code');
            $table->string('short_codename')->after('codename');
        });

        Schema::table('districts', function(Blueprint $table){

            $table->string('codename')->after('population');
            $table->string('short_codename')->after('population');
        });

        Schema::table('wards', function(Blueprint $table){

            $table->string('codename')->after('population');
            $table->string('short_codename')->after('population');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //Xoá column 
        Schema::table('provinces', function (Blueprint $table) {
            $table->dropColumn('codename');
            $table->dropColumn('short_codename');
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->dropColumn('codename');
            $table->dropColumn('short_codename');
        });

        Schema::table('wards', function (Blueprint $table) {
            $table->dropColumn('codename');
            $table->dropColumn('short_codename');
        });
    }
};
