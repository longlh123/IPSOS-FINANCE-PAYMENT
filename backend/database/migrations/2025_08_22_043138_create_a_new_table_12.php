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
        Schema::create('project_respondent_tokens', function(Blueprint $table) {
            
            $table->id();
            $table->foreignId('project_respondent_id')->constrained('project_respondents')->onDelete('cascade');
            $table->string('token_public')->unique();
            $table->string('token_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->enum('status', ['active','blocked'])->default('active');

            $table->timestamps();

            $table->unique('project_respondent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_respondent_tokens');                                                                                                                                                       
    }
};
