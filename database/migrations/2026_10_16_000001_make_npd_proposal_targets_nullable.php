<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Target COGS / HPP dan Target Selling Price di NPD Proposal menjadi opsional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('npd_proposals', function (Blueprint $table) {
            $table->decimal('target_cogs', 14, 2)->nullable()->change();
            $table->decimal('target_selling_price', 14, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Isi nilai kosong dengan 0 dulu agar kolom bisa kembali NOT NULL
        DB::table('npd_proposals')->whereNull('target_cogs')->update(['target_cogs' => 0]);
        DB::table('npd_proposals')->whereNull('target_selling_price')->update(['target_selling_price' => 0]);

        Schema::table('npd_proposals', function (Blueprint $table) {
            $table->decimal('target_cogs', 14, 2)->nullable(false)->change();
            $table->decimal('target_selling_price', 14, 2)->nullable(false)->change();
        });
    }
};
