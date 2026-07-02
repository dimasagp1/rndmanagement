<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trial_rms', function (Blueprint $table) {
            $table->text('trial_objective')->nullable()->after('sample_identity');
            $table->string('batch_qty')->nullable()->after('trial_objective');
            $table->string('packaging_design')->nullable()->after('batch_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trial_rms', function (Blueprint $table) {
            $table->dropColumn(['trial_objective', 'batch_qty', 'packaging_design']);
        });
    }
};
