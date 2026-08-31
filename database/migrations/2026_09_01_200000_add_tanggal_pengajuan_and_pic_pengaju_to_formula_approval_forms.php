<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->date('tanggal_pengajuan')->nullable()->after('final_approved_at');
            $table->string('pic_pengaju')->nullable()->after('tanggal_pengajuan');
        });
    }

    public function down(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->dropColumn(['tanggal_pengajuan', 'pic_pengaju']);
        });
    }
};
