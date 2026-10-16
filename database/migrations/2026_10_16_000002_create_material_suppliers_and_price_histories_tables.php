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
        // 1. Relasi Bahan Baku <-> Pemasok beserta Harga Aktif Terkini
        Schema::create('material_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->string('vendor_item_code', 100)->nullable();
            $table->decimal('current_price', 15, 2)->default(0);
            $table->string('currency', 10)->default('IDR');
            $table->decimal('moq', 10, 2)->nullable()->comment('Minimum Order Quantity');
            $table->unsignedInteger('lead_time_days')->nullable()->comment('Lead time pengiriman dalam hari');
            $table->date('effective_date')->nullable();
            $table->enum('price_source', ['manual_rnd', 'procurement_api'])->default('manual_rnd');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['material_id', 'supplier_id'], 'mat_sup_unique');
        });

        // 2. Riwayat Perubahan Harga (Price History / Audit Trail)
        Schema::create('material_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_supplier_id')->constrained('material_suppliers')->cascadeOnDelete();
            $table->decimal('old_price', 15, 2)->default(0);
            $table->decimal('new_price', 15, 2)->default(0);
            $table->decimal('price_diff', 15, 2)->default(0);
            $table->decimal('change_percentage', 6, 2)->default(0);
            $table->string('change_reason', 255)->nullable();
            $table->enum('source', ['manual_rnd', 'procurement_api'])->default('manual_rnd');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('effective_date')->nullable();
            $table->timestamps();
        });

        // 3. Staging / Feed Integrasi Harga Vendor dari Procurement
        Schema::create('procurement_vendor_prices', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 100)->nullable();
            $table->date('po_date')->nullable();
            $table->string('vendor_code', 100)->nullable();
            $table->string('vendor_name', 255);
            $table->string('material_code', 100)->nullable();
            $table->string('material_name', 255);
            $table->decimal('price_per_unit', 15, 2)->default(0);
            $table->string('unit', 50)->default('kg');
            $table->string('currency', 10)->default('IDR');
            $table->foreignId('matched_material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->foreignId('matched_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->enum('sync_status', ['unmapped', 'mapped', 'applied'])->default('unmapped');
            $table->json('raw_payload')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_vendor_prices');
        Schema::dropIfExists('material_price_histories');
        Schema::dropIfExists('material_suppliers');
    }
};
