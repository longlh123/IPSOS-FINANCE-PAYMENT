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
        Schema::create('custom_vouchers_token', function(Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('phone_number')->unique();
            $table->string('token')->unique();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->string('batch_id');
            $table->string('link')->nullable();
            $table->enum('status', ['active','blocked'])->default('active');
            $table->timestamps();
        });

        Schema::create('custom_vouchers_token_logs', function(Blueprint $table) {
            $table->id();
            $table->foreignId('token_id')->constrained('custom_vouchers_token')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('custom_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('token_id')->nullable()->unique()->constrained('custom_vouchers_token');
            $table->uuid('uuid')->unique();
            $table->string('batch_id');
            $table->string('code')->unique();
            $table->date('expired_from')->nullable();
            $table->date('expired_to')->nullable();
            $table->string('status')->default('unused'); //unused - reserved - sent 
            $table->timestamp('sent_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['batch_id','code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_vouchers_token');
        Schema::dropIfExists('custom_vouchers_token_logs');
        Schema::dropIfExists('custom_vouchers');
    }
};
