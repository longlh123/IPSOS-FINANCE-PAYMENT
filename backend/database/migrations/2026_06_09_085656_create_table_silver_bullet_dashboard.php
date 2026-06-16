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
        Schema::create('silver_bullet_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('respondent_id');
            $table->bigInteger('brand_code');
            $table->string('brand_name');
            $table->string('pack_type');
            $table->string('pack_size');
            $table->decimal('quantity', 10, 2);
            $table->date('recorded_date');
            $table->string('time_of_day');
            $table->timestamps();

            $table->index(['respondent_id', 'recorded_date', 'brand_code']);
            $table->index(['brand_code', 'recorded_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('silver_bullet_data');
    }
};
