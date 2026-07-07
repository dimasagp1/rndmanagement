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
            DB::statement("ALTER TABLE formulas MODIFY COLUMN development_stage ENUM('Draf', 'Pra-Trial', 'Optimalisasi', 'Final', 'Product Form', 'Laboratory Trial', 'Sensory Test', 'Plant Trial', 'Market Test') NOT NULL DEFAULT 'Draf'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE formulas MODIFY COLUMN development_stage ENUM('Draf', 'Pra-Trial', 'Optimalisasi', 'Final') NOT NULL DEFAULT 'Draf'");
        }
    }
};
