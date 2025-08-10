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
        Schema::table('project_vinnet_transactions', function(Blueprint $table){

            $table->enum('recipient_type', ['TS', 'TT', 'NT'])->default('NT')->after('payment_amt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //Xoá column 
        Schema::table('project_vinnet_transactions', function (Blueprint $table) {
            $table->dropColumn('recipient_type');
        });
    }
};
