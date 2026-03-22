<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // No-op: the unique index already exists in
        // 2024_08_21_042658_create_project_gotit_transactions_11.php.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for symmetry with up().
    }
};
