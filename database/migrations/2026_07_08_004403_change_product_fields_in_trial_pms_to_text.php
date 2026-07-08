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
        Schema::table('trial_pms', function (Blueprint $table) {
            $table->text('product_use')->nullable()->change();
            $table->text('product_trial')->nullable()->change();
        });

        // Convert existing records to JSON arrays if they are not already
        $records = DB::table('trial_pms')->get();
        foreach ($records as $record) {
            $productUse = $record->product_use;
            $productTrial = $record->product_trial;

            $newUse = $productUse;
            if ($productUse !== null && $productUse !== '') {
                $trimmed = trim($productUse);
                if (!str_starts_with($trimmed, '[') && !str_ends_with($trimmed, ']')) {
                    $newUse = json_encode([$productUse]);
                }
            } else {
                $newUse = json_encode([]);
            }

            $newTrial = $productTrial;
            if ($productTrial !== null && $productTrial !== '') {
                $trimmed = trim($productTrial);
                if (!str_starts_with($trimmed, '[') && !str_ends_with($trimmed, ']')) {
                    $newTrial = json_encode([$productTrial]);
                }
            } else {
                $newTrial = json_encode([]);
            }

            if ($newUse !== $productUse || $newTrial !== $productTrial) {
                DB::table('trial_pms')
                    ->where('id', $record->id)
                    ->update([
                        'product_use' => $newUse,
                        'product_trial' => $newTrial,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trial_pms', function (Blueprint $table) {
            $table->string('product_use')->default('')->change();
            $table->string('product_trial')->default('')->change();
        });
    }
};
