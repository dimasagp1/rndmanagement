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
        // 1. Tambah status pada Bahan Baku (materials)
        Schema::table('materials', function (Blueprint $table) {
            $table->enum('status', ['commercial', 'trial'])->default('commercial')->after('unit')->index();
        });

        // 2. Tambah status pada Pemasok (suppliers)
        Schema::table('suppliers', function (Blueprint $table) {
            $table->enum('status', ['commercial', 'trial'])->default('commercial')->after('name')->index();
        });

        // 3. Tambah status pada Relasi Harga Bahan Pemasok (material_suppliers)
        Schema::table('material_suppliers', function (Blueprint $table) {
            $table->enum('status', ['commercial', 'trial'])->default('commercial')->after('price_source')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_suppliers', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
