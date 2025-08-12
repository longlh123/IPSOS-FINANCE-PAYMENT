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
        Schema::table('project_respondents', function(Blueprint $table){

            $table->unique(['project_id', 'respondent_id'], 'unique_project_resp_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_respondents', function (Blueprint $table) {
            $table->dropUnique('unique_project_resp_id');
        });
    }
};
