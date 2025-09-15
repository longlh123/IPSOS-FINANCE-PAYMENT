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
        Schema::create('transportation_costs', function(Blueprint $table) {

            $table->id();
            $table->foreignId('origin_province_id')->constrained('provinces')->onDelete('cascade');
            $table->foreignId('destination_province_id')->constrained('provinces')->onDelete('cascade');
            $table->foreignId('destination_district_id')->constrained('districts')->onDelete('cascade');
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('bike_cost_min', 15, 2)->nullable();
            $table->decimal('bike_cost_max', 15, 2)->nullable();
            $table->decimal('bus_cost_min', 15, 2)->nullable();
            $table->decimal('bus_cost_max', 15, 2)->nullable();
            $table->decimal('plane_cost_min', 15, 2)->nullable();
            $table->decimal('plane_cost_max', 15, 2)->nullable();
            $table->decimal('train_cost_min', 15, 2)->nullable();
            $table->decimal('train_cost_max', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportation_costs');                                                                                                                                                       
    }
};
