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
        Schema::table('formulas', function (Blueprint $table) {
            $table->decimal('target_dose_a', 10, 4)->default(2.0000)->after('notes');
            $table->decimal('target_dose_b', 10, 4)->default(0.5000)->after('target_dose_a');
            $table->integer('target_sachet')->default(30)->after('target_dose_b');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formulas', function (Blueprint $table) {
            $table->dropColumn(['target_dose_a', 'target_dose_b', 'target_sachet']);
        });
    }
};
