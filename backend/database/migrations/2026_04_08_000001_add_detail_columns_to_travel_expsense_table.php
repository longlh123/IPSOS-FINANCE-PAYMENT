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
            if (!Schema::hasColumn('travel_expsense', 'departure_date')) {
                $table->date('departure_date')->nullable()->after('province_id');
            }

            if (!Schema::hasColumn('travel_expsense', 'return_date')) {
                $table->date('return_date')->nullable()->after('departure_date');
            }

            if (!Schema::hasColumn('travel_expsense', 'working_days')) {
                $table->decimal('working_days', 8, 2)->nullable()->after('return_date');
            }

            if (!Schema::hasColumn('travel_expsense', 'unit_price')) {
                $table->unsignedBigInteger('unit_price')->nullable()->after('working_days');
            }

            if (!Schema::hasColumn('travel_expsense', 'vehicle_ticket')) {
                $table->unsignedBigInteger('vehicle_ticket')->nullable()->after('unit_price');
            }

            if (!Schema::hasColumn('travel_expsense', 'unit_price_2')) {
                $table->unsignedBigInteger('unit_price_2')->nullable()->after('vehicle_ticket');
            }

            if (!Schema::hasColumn('travel_expsense', 'pickup_guests')) {
                $table->unsignedTinyInteger('pickup_guests')->nullable()->after('unit_price_2');
            }

            if (!Schema::hasColumn('travel_expsense', 'unit_price_3')) {
                $table->unsignedBigInteger('unit_price_3')->nullable()->after('pickup_guests');
            }

            if (!Schema::hasColumn('travel_expsense', 'total_salary')) {
                $table->unsignedBigInteger('total_salary')->nullable()->after('unit_price_3');
            }

            if (!Schema::hasColumn('travel_expsense', 'tax_deduction')) {
                $table->unsignedBigInteger('tax_deduction')->nullable()->after('total_salary');
            }

            if (!Schema::hasColumn('travel_expsense', 'remaining_amount')) {
                $table->unsignedBigInteger('remaining_amount')->nullable()->after('tax_deduction');
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
            if (Schema::hasColumn('travel_expsense', 'remaining_amount')) {
                $table->dropColumn('remaining_amount');
            }

            if (Schema::hasColumn('travel_expsense', 'tax_deduction')) {
                $table->dropColumn('tax_deduction');
            }

            if (Schema::hasColumn('travel_expsense', 'total_salary')) {
                $table->dropColumn('total_salary');
            }

            if (Schema::hasColumn('travel_expsense', 'unit_price_3')) {
                $table->dropColumn('unit_price_3');
            }

            if (Schema::hasColumn('travel_expsense', 'pickup_guests')) {
                $table->dropColumn('pickup_guests');
            }

            if (Schema::hasColumn('travel_expsense', 'unit_price_2')) {
                $table->dropColumn('unit_price_2');
            }

            if (Schema::hasColumn('travel_expsense', 'vehicle_ticket')) {
                $table->dropColumn('vehicle_ticket');
            }

            if (Schema::hasColumn('travel_expsense', 'unit_price')) {
                $table->dropColumn('unit_price');
            }

            if (Schema::hasColumn('travel_expsense', 'working_days')) {
                $table->dropColumn('working_days');
            }

            if (Schema::hasColumn('travel_expsense', 'return_date')) {
                $table->dropColumn('return_date');
            }

            if (Schema::hasColumn('travel_expsense', 'departure_date')) {
                $table->dropColumn('departure_date');
            }
        });
    }
};