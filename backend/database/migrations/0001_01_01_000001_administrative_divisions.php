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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('eng_name');
            $table->timestamps();
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('abbreviation');
            $table->integer('old_area_code');
            $table->integer('area_code');
            $table->string('codename');
            $table->string('short_codename');
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('districts', function(Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->string('name');
            $table->string('code');
            $table->float('land_area');
            $table->integer('population');
            $table->string('codename');
            $table->string('short_codename');
            $table->timestamps();
        });

        Schema::create('wards', function(Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->string('name');
            $table->string('code');
            $table->float('land_area');
            $table->integer('population');
            $table->string('codename');
            $table->string('short_codename');
            $table->timestamps();
        });
        
        Schema::create('gso2025_provinces', function(Blueprint $table) {
            $table->id();
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

        Schema::create('province_mapping', function(Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->foreignId('gso2025_province_id')->constrained('gso2025_provinces')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['province_id', 'gso2025_province_id'], 'unique_provinces');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('wards');
        Schema::dropIfExists('gso2025_provinces');
        Schema::dropIfExists('gso2025_districts');
        Schema::dropIfExists('province_mapping');    
    }
};
