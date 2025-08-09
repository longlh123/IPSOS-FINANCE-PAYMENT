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
        Schema::create('project_vinnet_transactions', function(Blueprint $table) {
            $table->id();
            $table->foreignId('project_respondent_id')->constrained('project_respondents')->onDelete('cascade');
            $table->string('vinnet_serviceitems_requuid')->nullable();
            $table->string('vinnet_payservice_requuid')->nullable();
            $table->string('vinnet_token_requuid');
            $table->string('vinnet_token');
            $table->integer('vinnet_token_order');
            $table->string('vinnet_token_status')->nullable(); 
            $table->string('vinnet_token_message')->nullable();
            $table->double('total_amt')->default(0.0);
            $table->double('commission')->default(0.0);
            $table->double('discount')->default(0.0);
            $table->double('payment_amt')->default(0.0);
            $table->datetime('vinnet_invoice_date')->nullable();
            $table->timestamps();

            $table->unique(['project_respondent_id', 'vinnet_token_order'], 'unique_project_vinnet_transactions_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_vinnet_transactions');
    }
};
