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
        Schema::create('project_types', function(Blueprint $table){
            $table->id();
            $table->string('name')->unique();
            $table->string('title')->nullable();
            $table->timestamps();
        });
        
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('internal_code');
            $table->string('project_name');
            $table->boolean('disabled')->default(false);
            $table->timestamps();
        
            $table->unique(['internal_code', 'project_name'], 'unique_internal_code_project_name');
        });

        Schema::create('project_details', function (Blueprint $table) {
            $table->id(); //Auto-incrementing primary key.
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('symphony')->nullable(); //Symphony (External code for the project)
            $table->string('job_number')->nullable(); //job number code for the project
            $table->enum('status', ['planned', 'in coming', 'on going', 'completed', 'on hold', 'cancelled'])->default('planned'); //status of the project
            $table->foreignId('created_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('platform', ['ifield', 'dimensions']); //platform the project run on
            $table->datetime('planned_field_start'); //start date of the project
            $table->datetime('planned_field_end'); //end date of the project
            $table->datetime('actual_field_start')->nullable(); //actual start date of the project
            $table->datetime('actual_field_end')->nullable(); //actual end date of the project
            $table->string('remember_token', 100);
            $table->string('remember_uuid');
            $table->timestamps();
        });
        
        Schema::create('project_project_types', function(Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('project_type_id')->constrained('project_types')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['project_id', 'project_type_id'], 'unique_project_project_types');
        });

        Schema::create('project_teams', function(Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['project_id', 'team_id'], 'unique_project_teams');
        });

        Schema::create('project_provinces', function(Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->integer('sample_size_main');
            $table->decimal('price_main', 15, 2);
            $table->decimal('price_main_1', 15, 2)->nullable();
            $table->decimal('price_main_2', 15, 2)->nullable();
            $table->decimal('price_main_3', 15, 2)->nullable();
            $table->decimal('price_main_4', 15, 2)->nullable();
            $table->decimal('price_main_5', 15, 2)->nullable();
            $table->integer('sample_size_booters')->nullable();
            $table->decimal('price_boosters', 15, 2)->nullable();
            $table->decimal('price_boosters_1', 15, 2)->nullable();
            $table->decimal('price_boosters_2', 15, 2)->nullable();
            $table->decimal('price_boosters_3', 15, 2)->nullable();
            $table->decimal('price_boosters_4', 15, 2)->nullable();
            $table->decimal('price_boosters_5', 15, 2)->nullable();
            $table->timestamps();
            
            $table->unique(['project_id', 'province_id'], 'unique_project_province');
        });
        
        Schema::create('project_permissions', function(Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['project_id', 'user_id'], 'unique_project_permissions');
        });

        Schema::create('project_employees', function(Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['project_id', 'employee_id'], 'unique_project_employees');
        });

        Schema::create('project_vinnet_tokens', function(Blueprint $table) {
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
            $table->string('vinnet_token_requuid');
            $table->string('vinnet_serviceitems_requuid')->nullable();
            $table->string('vinnet_payservice_requuid')->nullable();
            $table->string('vinnet_token'); //->unique(); 
            $table->integer('vinnet_token_order');
            $table->string('vinnet_token_status')->nullable(); 
            $table->string('status')->nullable();
            $table->double('total_amt')->default(0.0);
            $table->double('commission')->default(0.0);
            $table->double('discount')->default(0.0);
            $table->double('payment_amt')->default(0.0);
            $table->datetime('vinnet_invoice_date')->nullable();
            $table->string('reject_message')->nullable();
            $table->timestamps();

            //Unique constraint for vinnet_token within each combination of internal_code, project_name, respondent_id
            $table->unique(['project_id', 'respondent_id', 'vinnet_token_order'], 'unique_project_resp_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_types');
        Schema::dropIfExists('projects');   
        Schema::dropIfExists('project_details');
        Schema::dropIfExists('project_project_types');
        Schema::dropIfExists('project_provinces');
        Schema::dropIfExists('project_permissions');
        Schema::dropIfExists('project_employees');
        Schema::dropIfExists('project_vinnet_tokens');
    }
};
