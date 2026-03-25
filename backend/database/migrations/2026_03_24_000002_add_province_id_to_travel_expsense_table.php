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
        if (!Schema::hasTable('travel_expsense')) {
            return;
        }

        Schema::table('travel_expsense', function (Blueprint $table) {
            if (!Schema::hasColumn('travel_expsense', 'province_id')) {
                $table->foreignId('province_id')->nullable()->after('employee_id')->constrained('provinces')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('travel_expsense')) {
            return;
        }

        Schema::table('travel_expsense', function (Blueprint $table) {
            if (Schema::hasColumn('travel_expsense', 'province_id')) {
                $table->dropForeign(['province_id']);
                $table->dropColumn('province_id');
            }
        });
    }
};