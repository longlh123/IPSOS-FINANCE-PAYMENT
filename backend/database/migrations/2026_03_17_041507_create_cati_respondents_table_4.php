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
        Schema::create('cati_respondents', function (Blueprint $table) {
            $table->id();
            $table->string('respondent_id')->unique();
            $table->string('phone');
            $table->string('name')->nullable();
            $table->string('link')->nullable();
            $table->string('filter_1')->nullable();
            $table->string('filter_2')->nullable();
            $table->string('filter_3')->nullable();
            $table->string('filter_4')->nullable();
            $table->string('status')->default('New'); // New, Calling, Done...
            $table->text('comment')->nullable();
            $table->string('assigned_to')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cati_respondents');
    }
};
