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
        if (!Schema::hasTable('project_details')) {
            return;
        }

        Schema::table('project_details', function (Blueprint $table) {
            if (!Schema::hasColumn('project_details', 'travel_expense_target')) {
                $table->unsignedInteger('travel_expense_target')->nullable()->after('project_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('project_details')) {
            return;
        }

        Schema::table('project_details', function (Blueprint $table) {
            if (Schema::hasColumn('project_details', 'travel_expense_target')) {
                $table->dropColumn('travel_expense_target');
            }
        });
    }
};