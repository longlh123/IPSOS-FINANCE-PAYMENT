<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('fm_confirmed_user_id')->nullable()->constrained('users')->after('approved_at');
            $table->timestamp('fm_confirmed_at')->nullable()->after('fm_confirmed_user_id');
        });

        // Extend enum to include fm_confirmed (MySQL only; SQLite ignores enum constraints)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft','submitted','fm_confirmed','approved','rejected') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['fm_confirmed_user_id']);
            $table->dropColumn(['fm_confirmed_user_id', 'fm_confirmed_at']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft'");
        }
    }
};
