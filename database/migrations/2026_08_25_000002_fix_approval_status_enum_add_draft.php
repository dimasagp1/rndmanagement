<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL enum alteration: add 'Draft' to approval_status (skip on sqlite for tests)
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE formula_approval_forms MODIFY approval_status ENUM('Draft','Pending','Approval by OM','Approved','Rejected') NOT NULL DEFAULT 'Draft'");
        }
    }

    public function down(): void
    {
        // Revert but keep Draft rows -> convert to Pending before downgrade
        DB::table('formula_approval_forms')->where('approval_status', 'Draft')->update(['approval_status' => 'Pending']);
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE formula_approval_forms MODIFY approval_status ENUM('Pending','Approval by OM','Approved','Rejected') NOT NULL DEFAULT 'Pending'");
        }
    }
};
