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
        Schema::create('gso2025_provinces', function(Blueprint $table) {
            $table->id();
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
        Schema::dropIfExists('gso2025_provinces');
        Schema::dropIfExists('province_mapping');                                                                                                                                                          
    }
};
