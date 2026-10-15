<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lindungi data dari penghapusan berantai (temuan K-05 & K-06 inspeksi kode).
 *
 * - users.is_active: akun dinonaktifkan, bukan dihapus.
 * - FK pembuat/pemilik data ke users: cascade → restrict, agar menghapus user
 *   tidak ikut menghapus formula, trial, PRF, NPD, Log Book, dan Sample Evaluation.
 * - formula_materials.material_id / supplier_id: cascade → restrict, agar bahan
 *   baku atau supplier yang dipakai formula tidak bisa dihapus. supplier_id dibuat
 *   nullable.
 */
return new class extends Migration
{
    /** [tabel, kolom] FK ke users yang sebelumnya cascade */
    private const USER_FOREIGN_KEYS = [
        ['formulas', 'created_by'],
        ['trial_rms', 'created_by'],
        ['trial_pms', 'created_by'],
        ['logbook_pms', 'created_by'],
        ['prfs', 'created_by'],
        ['npd_proposals', 'created_by'],
        ['sample_evaluations', 'created_by'],
        ['sample_evaluations', 'project_owner_id'],
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('password');
        });

        foreach (self::USER_FOREIGN_KEYS as [$table, $column]) {
            $this->replaceForeignKey($table, $column, 'users', 'restrict');
        }

        $this->dropForeignIfExists('formula_materials', 'material_id');
        $this->dropForeignIfExists('formula_materials', 'supplier_id');

        Schema::table('formula_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable()->change();
        });

        Schema::table('formula_materials', function (Blueprint $table) {
            $table->foreign('material_id')->references('id')->on('materials')->restrictOnDelete();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        $this->dropForeignIfExists('formula_materials', 'material_id');
        $this->dropForeignIfExists('formula_materials', 'supplier_id');

        Schema::table('formula_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable(false)->change();
        });

        Schema::table('formula_materials', function (Blueprint $table) {
            $table->foreign('material_id')->references('id')->on('materials')->cascadeOnDelete();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
        });

        foreach (self::USER_FOREIGN_KEYS as [$table, $column]) {
            $this->replaceForeignKey($table, $column, 'users', 'cascade');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    private function replaceForeignKey(string $table, string $column, string $references, string $onDelete): void
    {
        $this->dropForeignIfExists($table, $column);

        Schema::table($table, function (Blueprint $t) use ($column, $references, $onDelete) {
            $t->foreign($column)->references('id')->on($references)->onDelete($onDelete);
        });
    }

    private function dropForeignIfExists(string $table, string $column): void
    {
        $constraint = "{$table}_{$column}_foreign";

        // MySQL: pastikan constraint memang ada (pola yang sama dengan migration PRF/NPD)
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $exists = DB::table('information_schema.table_constraints')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $table)
                ->where('constraint_name', $constraint)
                ->where('constraint_type', 'FOREIGN KEY')
                ->exists();

            if (! $exists) {
                return;
            }
        }

        Schema::table($table, function (Blueprint $t) use ($column) {
            $t->dropForeign([$column]);
        });
    }
};
