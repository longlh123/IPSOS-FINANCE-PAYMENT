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
        Schema::create('project_iuu_respondents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number');
            $table->string('service_code');
            $table->enum('channel', ['gotit', 'vinnet']);
            $table->unsignedInteger('gift_denomination'); // Mệnh giá quà tặng
            $table->unsignedInteger('send_count'); //Số lần gửi trên mệnh giá quà tặng
            $table->text('quota_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_iuu_respondents');
    }
};
