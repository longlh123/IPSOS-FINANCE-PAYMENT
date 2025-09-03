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

            $table->string('code')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //Xoá column 
        Schema::table('provinces', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
