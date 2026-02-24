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
        Schema::create('project_provinces_new', function(Blueprint $table) {
            
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->enum('price_item', ['main','main_1','main_2','main_3','main_4','main_5','boosters','boosters_1','boosters_2','boosters_3','boosters_4','boosters_5','non','non_1','non_2','non_3','non_4','non_5']);
            $table->integer('sample_size');
            $table->decimal('amout', 15, 2);
            $table->timestamps();

            $table->unique(['project_id', 'province_id', 'price_item'], 'unique_project_province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_provinces_new');                                                                                                                                                          
    }
};
