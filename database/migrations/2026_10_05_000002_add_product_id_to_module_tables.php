<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES_WITH_BACKFILL_BY_NAME = [
        'formulas'              => 'name',
        'prfs'                  => 'product_name',
        'npd_proposals'         => 'product_name',
        'preformulation_studies' => 'product_name',
        'sample_evaluations'    => 'product_name',
        'qbds'                  => 'product_name',
        'nie_approvals'         => 'product_name',
    ];

    private const TABLES_WITHOUT_BACKFILL = [
        'trial_pms',
        'stability_tests',
        'technology_transfers',
    ];

    public function up(): void
    {
        // 1. Add product_id column to all tables
        foreach (array_keys(self::TABLES_WITH_BACKFILL_BY_NAME) as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('product_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('products')
                    ->nullOnDelete();
                $t->index('product_id');
            });
        }

        foreach (self::TABLES_WITHOUT_BACKFILL as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('product_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('products')
                    ->nullOnDelete();
                $t->index('product_id');
            });
        }

        // trial_rms: backfill via formula_id → formulas.product_id
        Schema::table('trial_rms', function (Blueprint $t) {
            $t->foreignId('product_id')
                ->nullable()
                ->after('id')
                ->constrained('products')
                ->nullOnDelete();
            $t->index('product_id');
        });

        DB::table('trial_rms')
            ->whereNull('product_id')
            ->update([
                'product_id' => DB::raw(
                    '(SELECT product_id FROM formulas WHERE formulas.id = trial_rms.formula_id LIMIT 1)'
                ),
            ]);

        // 2. Backfill name-match tables
        foreach (self::TABLES_WITH_BACKFILL_BY_NAME as $table => $column) {
            DB::table($table)
                ->whereNull('product_id')
                ->update([
                    'product_id' => DB::raw(
                        "(SELECT id FROM products WHERE products.name = {$table}.{$column} LIMIT 1)"
                    ),
                ]);
        }
    }

    public function down(): void
    {
        $tables = array_merge(
            array_keys(self::TABLES_WITH_BACKFILL_BY_NAME),
            self::TABLES_WITHOUT_BACKFILL,
            ['trial_rms']
        );

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['product_id']);
                $t->dropColumn('product_id');
            });
        }
    }
};
