<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert existing text to valid json array format first
        foreach (DB::table('trial_pms')->get() as $row) {
            $spec = $row->specifications;
            if ($spec) {
                json_decode($spec);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $newSpec = json_encode([$spec]);
                    DB::table('trial_pms')->where('id', $row->id)->update([
                        'specifications' => $newSpec
                    ]);
                }
            } else {
                DB::table('trial_pms')->where('id', $row->id)->update([
                    'specifications' => json_encode([])
                ]);
            }
        }

        Schema::table('trial_pms', function (Blueprint $table) {
            if (!Schema::hasColumn('trial_pms', 'proposal_number')) {
                $table->string('proposal_number')->nullable()->after('code');
            }
            if (!Schema::hasColumn('trial_pms', 'supplier')) {
                $table->string('supplier')->after('packaging_material')->default('');
            }
            if (!Schema::hasColumn('trial_pms', 'product_use')) {
                $table->string('product_use')->after('supplier')->default('');
            }
            if (!Schema::hasColumn('trial_pms', 'product_trial')) {
                $table->string('product_trial')->after('product_use')->default('');
            }
            if (!Schema::hasColumn('trial_pms', 'trial_sample_quantity')) {
                $table->string('trial_sample_quantity')->after('product_trial')->default('');
            }
            if (!Schema::hasColumn('trial_pms', 'old_supplier')) {
                $table->string('old_supplier')->nullable()->after('trial_sample_quantity');
            }
            if (!Schema::hasColumn('trial_pms', 'difference_with_existing')) {
                $table->text('difference_with_existing')->nullable()->after('old_supplier');
            }

            // Change specifications to json (nullable)
            $table->json('specifications')->nullable()->change();

            if (!Schema::hasColumn('trial_pms', 'executions')) {
                $table->json('executions')->nullable()->after('specifications');
            }
            if (!Schema::hasColumn('trial_pms', 'discussion_rows')) {
                $table->json('discussion_rows')->nullable()->after('risk_analysis');
            }
            if (!Schema::hasColumn('trial_pms', 'photos')) {
                $table->json('photos')->nullable()->after('discussion_rows');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trial_pms', function (Blueprint $table) {
            $table->dropColumn([
                'proposal_number',
                'supplier',
                'product_use',
                'product_trial',
                'trial_sample_quantity',
                'old_supplier',
                'difference_with_existing',
                'executions',
                'discussion_rows',
                'photos',
            ]);
            $table->text('specifications')->change();
        });
    }
};
