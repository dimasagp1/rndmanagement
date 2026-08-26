<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->string('sample_code')->nullable()->after('komoditi');
            $table->text('decision_reason')->nullable()->after('rejection_notes');
            $table->text('gm_suggestions')->nullable()->after('decision_reason');
        });
    }

    public function down(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->dropColumn(['sample_code', 'decision_reason', 'gm_suggestions']);
        });
    }
};
