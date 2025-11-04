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
        Schema::create('project_respondents', function (Blueprint $table) {
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
            $table->string('service_type', 50)->nullable();
            $table->string('service_code');
            $table->text('reject_message')->nullable();
            $table->enum('channel', ['gotit', 'vinnet']);
            $table->enum('price_level', ['main','main_1','main_2','main_3','main_4','main_5','booster','booster_1','booster_2','booster_3','booster_4','booster_5']);
            $table->text('status');
            $table->timestamps();

            $table->unique(['project_id', 'respondent_id'], 'unique_project_resp_id');
        });

        Schema::create('project_gotit_voucher_transactions', function(Blueprint $table) {
            
            $table->id();
            $table->foreignId('project_respondent_id')->constrained('project_respondents')->onDelete('cascade');
            $table->string('transaction_ref_id')->nullable();
            $table->integer('transaction_ref_id_order')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('order_name')->nullable();
            $table->double('amount')->default(0.0);
            $table->string('voucher_link_group')->nullable();
            $table->string('voucher_link_code_group')->nullable();
            $table->string('voucher_serial_group')->nullable();
            $table->string('voucher_code')->nullable();
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
            $table->datetime('invoice_date')->nullable();
            $table->string('invoice_comment')->nullable();
            $table->timestamps();

            $table->unique(['project_respondent_id', 'transaction_ref_id', 'transaction_ref_id_order'], 'unique_project_gotit_voucher_transactions_id');
        });

        Schema::create('project_gotit_sms_transactions', function(Blueprint $table) {
            
            $table->id();
            $table->foreignId('voucher_transaction_id')->constrained('project_gotit_voucher_transactions')->onDelete('cascade');
            $table->string('transaction_ref_id')->nullable();
            $table->string('sms_status')->nullable();
            $table->timestamps();

            $table->unique(['voucher_transaction_id', 'transaction_ref_id'], 'unique_project_gotit_sms_transactions_id');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_respondents');
        Schema::dropIfExists('project_gotit_voucher_transactions');
        Schema::dropIfExists('project_gotit_sms_transactions');
    }
};
