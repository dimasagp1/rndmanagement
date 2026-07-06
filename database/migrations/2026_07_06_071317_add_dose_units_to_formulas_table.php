<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formulas', function (Blueprint $table) {
            $table->string('target_dose_a_unit', 20)->default('g')->after('target_dose_a');
            $table->string('target_dose_b_unit', 20)->default('g')->after('target_dose_b');
            $table->string('target_sachet_unit', 30)->default('sachet')->after('target_sachet');
        });
    }

    public function down(): void
    {
        Schema::table('formulas', function (Blueprint $table) {
            $table->dropColumn(['target_dose_a_unit', 'target_dose_b_unit', 'target_sachet_unit']);
        });
    }
};
