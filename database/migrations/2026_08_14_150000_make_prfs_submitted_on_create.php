<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PRF tidak lagi melalui tahap Draft: semua PRF langsung berstatus
        // Submitted saat dibuat. Mapping data lama Draft -> Submitted,
        // lalu persempit enum menjadi hanya ['Submitted'].
        DB::table('prfs')
            ->where('approval_status', 'Draft')
            ->update(['approval_status' => 'Submitted']);

        Schema::table('prfs', function (Blueprint $table) {
            $table->enum('approval_status', ['Submitted'])->default('Submitted')->change();
        });

        // Pengaman: PRF yang sudah dipakai sebagai dasar NPD Proposal
        // tidak boleh dihapus (restrict), bukan ikut terhapus (cascade).
        $isMySql = Schema::getConnection()->getDriverName() === 'mysql';

        if ($isMySql) {
            $hasFk = DB::table('information_schema.table_constraints')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', 'npd_proposals')
                ->where('constraint_name', 'npd_proposals_prf_id_foreign')
                ->exists();

            $hasIndex = DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', 'npd_proposals')
                ->where('index_name', 'npd_proposals_prf_id_foreign')
                ->exists();

            Schema::table('npd_proposals', function (Blueprint $table) use ($hasFk, $hasIndex) {
                if ($hasFk) {
                    $table->dropForeign(['prf_id']);
                } elseif ($hasIndex) {
                    $table->dropIndex('npd_proposals_prf_id_foreign');
                }
            });

            // Bersihkan NPD Proposal yatim (prf_id mengarah ke PRF yang sudah
            // dihapus) agar FK baru dapat ditambahkan.
            DB::table('npd_proposals')
                ->whereNotIn('prf_id', DB::table('prfs')->select('id'))
                ->delete();
        }

        Schema::table('npd_proposals', function (Blueprint $table) {
            $table->foreign('prf_id')
                ->references('id')
                ->on('prfs')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('npd_proposals', function (Blueprint $table) {
            $table->dropForeign(['prf_id']);
            $table->foreign('prf_id')
                ->references('id')
                ->on('prfs')
                ->onDelete('cascade');
        });

        Schema::table('prfs', function (Blueprint $table) {
            $table->enum('approval_status', ['Draft', 'Submitted'])->default('Draft')->change();
        });
    }
};