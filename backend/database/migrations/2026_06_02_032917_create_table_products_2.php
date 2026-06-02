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
        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_en')->unique();
            $table->string('name_vi')->unique();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('industry_id')->constrained('industries')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('name_en');
            $table->timestamps();

            $table->unique(['industry_id', 'name_en']);
            $table->unique(['industry_id', 'name']);
            $table->unique(['industry_id', 'code']);
            $table->index('industry_id');
            $table->index('name_en');
            $table->index('name');
        });

        Schema::create('subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('name_en');
            $table->timestamps();

            $table->unique(['category_id', 'name_en']);
            $table->unique(['category_id', 'name']);
            $table->unique(['category_id', 'code']);
            $table->index('category_id');
            $table->index('name_en');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcategories');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('industries');
    }
};
