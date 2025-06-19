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
        Schema::create('project_gotit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('shell_chainid');
            $table->string('respondent_id');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->datetime('interview_start');
            $table->datetime('interview_end'); 
            $table->string('respondent_phone_number'); //Số điện thoại đáp viên, thu thập trong quá trình phỏng vấn
            $table->string('phone_number'); //Số điện thoại của đáp viên, được đáp viên xác nhận khi nhận quà
            $table->string('transaction_ref_id')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('order_name')->nullable();
            $table->double('amount')->default(0.0);
            $table->string('status');
            $table->string('voucher_link')->nullable();
            $table->string('voucher_link_code')->nullable();
            $table->text('voucher_image_link')->nullable();
            $table->string('voucher_cover_link')->nullable();
            $table->string('voucher_serial')->nullable();
            $table->date('voucher_expired_date')->nullable();
            $table->string('voucher_product_id')->nullable();
            $table->string('voucher_price_id')->nullable();
            $table->double('voucher_value')->default(0.0);
            $table->string('voucher_status')->nullable();
            $table->string('sms_status')->nullable();
            $table->datetime('invoice_date')->nullable();
            $table->string('reject_message')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'respondent_id'], 'unique_project_resp_id');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_gotit_transactions');
    }
};
