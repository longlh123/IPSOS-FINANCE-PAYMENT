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
        Schema::create('gotit_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_user_id')->constrained('users')->onDelete('cascade'); //Người tạo voucher
            $table->string('transaction_ref_id')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('order_name')->nullable();
            $table->double('amount')->default(0.0);
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
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
