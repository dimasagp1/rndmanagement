<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE logbook_pms MODIFY COLUMN status_pengujian ENUM('Pending', 'On Going Trial', 'Passed', 'Rejected') NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE logbook_pms MODIFY COLUMN status_pengujian ENUM('Pending', 'Proses', 'Lulus', 'Tidak Lulus') NOT NULL DEFAULT 'Pending'");
    }
};
