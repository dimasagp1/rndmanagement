<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->string('source')->default('formula-approval')->after('type');
        });

        // Existing records are from formula-approval (already have type='Formula')
        \DB::table('formula_approval_forms')->whereNull('source')->update(['source' => 'formula-approval']);
    }

    public function down(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
