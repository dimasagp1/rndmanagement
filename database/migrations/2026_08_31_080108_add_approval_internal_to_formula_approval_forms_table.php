<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->string('approval_internal')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->dropColumn('approval_internal');
        });
    }
};
