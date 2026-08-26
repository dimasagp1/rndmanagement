<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->enum('type', ['Formula', 'Design'])->default('Formula')->after('id');
        });

        // Data lama dianggap kategori Formula
        DB::table('formula_approval_forms')->whereNull('type')->update(['type' => 'Formula']);
    }

    public function down(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
