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
        Schema::create('project_vinnet_sms_transactions', function(Blueprint $table) {
            
            $table->id();
            $table->foreignId('vinnet_transaction_id')->constrained('project_vinnet_transactions')->onDelete('cascade');
            $table->string('sms_status')->nullable();
            $table->timestamps();

            $table->unique('vinnet_transaction_id', 'unique_project_gotit_sms_transactions_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_vinnet_sms_transactions');                                                                                                                                                       
    }
};
