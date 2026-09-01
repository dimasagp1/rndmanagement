<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE logbook_pms MODIFY COLUMN status_pengujian VARCHAR(50) NOT NULL DEFAULT 'Pending'");
        DB::statement("UPDATE logbook_pms SET status_pengujian = 'On Going Trial' WHERE status_pengujian = 'Proses'");
        DB::statement("UPDATE logbook_pms SET status_pengujian = 'Passed' WHERE status_pengujian = 'Lulus'");
        DB::statement("UPDATE logbook_pms SET status_pengujian = 'Rejected' WHERE status_pengujian = 'Tidak Lulus'");
        DB::statement("ALTER TABLE logbook_pms MODIFY COLUMN status_pengujian ENUM('Pending', 'On Going Trial', 'Passed', 'Rejected') NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE logbook_pms MODIFY COLUMN status_pengujian VARCHAR(50) NOT NULL DEFAULT 'Pending'");
        DB::statement("UPDATE logbook_pms SET status_pengujian = 'Proses' WHERE status_pengujian = 'On Going Trial'");
        DB::statement("UPDATE logbook_pms SET status_pengujian = 'Lulus' WHERE status_pengujian = 'Passed'");
        DB::statement("UPDATE logbook_pms SET status_pengujian = 'Tidak Lulus' WHERE status_pengujian = 'Rejected'");
        DB::statement("ALTER TABLE logbook_pms MODIFY COLUMN status_pengujian ENUM('Pending', 'Proses', 'Lulus', 'Tidak Lulus') NOT NULL DEFAULT 'Pending'");
    }
};
