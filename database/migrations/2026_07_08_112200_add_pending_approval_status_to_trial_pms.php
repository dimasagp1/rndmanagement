<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE trial_pms MODIFY COLUMN approval_status ENUM('Draft', 'Pending Review', 'Pending Approval', 'Approved', 'Rejected') NOT NULL DEFAULT 'Draft'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            // Note: If there are existing records with 'Pending Approval', they might be affected by downgrading.
            DB::statement("ALTER TABLE trial_pms MODIFY COLUMN approval_status ENUM('Draft', 'Pending Review', 'Approved', 'Rejected') NOT NULL DEFAULT 'Draft'");
        }
    }
};
