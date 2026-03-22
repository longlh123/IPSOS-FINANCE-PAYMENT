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
        if (!Schema::hasColumn('provinces', 'codename')) {
            Schema::table('provinces', function(Blueprint $table){
                $table->string('codename')->after('area_code');
            });
        }

        if (!Schema::hasColumn('provinces', 'short_codename')) {
            Schema::table('provinces', function(Blueprint $table){
                $table->string('short_codename')->after('codename');
            });
        }

        if (!Schema::hasColumn('districts', 'codename')) {
            Schema::table('districts', function(Blueprint $table){
                $table->string('codename')->after('population');
            });
        }

        if (!Schema::hasColumn('districts', 'short_codename')) {
            Schema::table('districts', function(Blueprint $table){
                $table->string('short_codename')->after('codename');
            });
        }

        if (!Schema::hasColumn('wards', 'codename')) {
            Schema::table('wards', function(Blueprint $table){
                $table->string('codename')->after('population');
            });
        }

        if (!Schema::hasColumn('wards', 'short_codename')) {
            Schema::table('wards', function(Blueprint $table){
                $table->string('short_codename')->after('codename');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //Xoá column 
        if (Schema::hasColumn('provinces', 'codename')) {
            Schema::table('provinces', function (Blueprint $table) {
                $table->dropColumn('codename');
            });
        }

        if (Schema::hasColumn('provinces', 'short_codename')) {
            Schema::table('provinces', function (Blueprint $table) {
                $table->dropColumn('short_codename');
            });
        }

        if (Schema::hasColumn('districts', 'codename')) {
            Schema::table('districts', function (Blueprint $table) {
                $table->dropColumn('codename');
            });
        }

        if (Schema::hasColumn('districts', 'short_codename')) {
            Schema::table('districts', function (Blueprint $table) {
                $table->dropColumn('short_codename');
            });
        }

        if (Schema::hasColumn('wards', 'codename')) {
            Schema::table('wards', function (Blueprint $table) {
                $table->dropColumn('codename');
            });
        }

        if (Schema::hasColumn('wards', 'short_codename')) {
            Schema::table('wards', function (Blueprint $table) {
                $table->dropColumn('short_codename');
            });
        }
    }
};
