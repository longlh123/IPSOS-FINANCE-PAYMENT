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
        Schema::create('districts', function(Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->string('name');
            $table->string('code');
            $table->float('land_area');
            $table->integer('population');
            $table->timestamps();
        });

        Schema::create('wards', function(Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->string('name');
            $table->string('code');
            $table->float('land_area');
            $table->integer('population');
            $table->timestamps();
        });

        Schema::create('gso2025_districts', function(Blueprint $table) {
            $table->id();
            $table->foreignId('gso2025_province_id')->constrained('gso2025_provinces')->onDelete('cascade');
            $table->string('name');
            $table->string('code');
            $table->float('land_area');
            $table->integer('population');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
        Schema::dropIfExists('wards'); 
        Schema::dropIfExists('gso2025_districts');                                                                                                                                                          
    }
};
