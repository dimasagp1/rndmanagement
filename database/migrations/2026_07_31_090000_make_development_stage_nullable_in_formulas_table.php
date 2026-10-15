<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STAGES = ['Draf', 'Pra-Trial', 'Optimalisasi', 'Final', 'Product Form', 'Laboratory Trial', 'Sensory Test', 'Plant Trial', 'Market Test'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE formulas MODIFY COLUMN development_stage ENUM('Draf', 'Pra-Trial', 'Optimalisasi', 'Final', 'Product Form', 'Laboratory Trial', 'Sensory Test', 'Plant Trial', 'Market Test') NULL DEFAULT NULL");
        } else {
            // SQLite (test): samakan dengan MySQL agar formula "existing" bisa tanpa development_stage
            Schema::table('formulas', function (Blueprint $table) {
                $table->enum('development_stage', self::STAGES)->nullable()->default(null)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE formulas MODIFY COLUMN development_stage ENUM('Draf', 'Pra-Trial', 'Optimalisasi', 'Final', 'Product Form', 'Laboratory Trial', 'Sensory Test', 'Plant Trial', 'Market Test') NOT NULL DEFAULT 'Draf'");
        } else {
            Schema::table('formulas', function (Blueprint $table) {
                $table->enum('development_stage', self::STAGES)->default('Draf')->change();
            });
        }
    }
};
