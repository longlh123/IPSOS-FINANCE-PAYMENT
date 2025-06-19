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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('date_of_birth')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->string('phone_number')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('tax_code')->nullable();
            $table->date('tax_deduction_at')->nullable();
            $table->string('card_id')->nullable();
            $table->string('citizen_identity_card')->nullable();
            $table->string('place_of_residence')->nullable();
            $table->date('date_of_issuance')->nullable();
            $table->string('place_of_issuance')->nullable();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
