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
        Schema::create('formula_approval_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->unique()->constrained('formulas')->onDelete('cascade');
            $table->string('product_name');
            $table->string('kategori')->nullable();
            $table->string('komoditi')->nullable();
            $table->string('bentuk_sediaan')->nullable();
            $table->string('manufactured')->nullable();
            $table->string('distributor')->nullable();
            $table->text('klaim_product')->nullable();
            $table->text('komposisi')->nullable();
            $table->string('aturan_pakai')->nullable();
            $table->string('ukuran_kemasan')->nullable();
            $table->string('packaging')->nullable();
            $table->text('sensory_product')->nullable();
            $table->date('target_launch')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formula_approval_forms');
    }
};