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
        Schema::create('formulas', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // FRM-YYYYMM-XXX
            $table->string('name'); // Nama Produk
            $table->integer('version')->default(1); // Versioning (V1, V2, V3...)
            $table->foreignId('parent_formula_id')->nullable()->constrained('formulas')->onDelete('set null'); // untuk tracking reformulasi
            $table->enum('development_stage', ['Draf', 'Pra-Trial', 'Optimalisasi', 'Final', 'Product Form', 'Laboratory Trial', 'Sensory Test', 'Plant Trial', 'Market Test'])->default('Draf');
            $table->enum('approval_status', ['Draft', 'Pending Tahap 1', 'Pending Tahap 2', 'Approved', 'Rejected'])->default('Draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by_om')->nullable()->constrained('users')->onDelete('set null'); // Operational Manager
            $table->foreignId('approved_by_gm')->nullable()->constrained('users')->onDelete('set null'); // General Manager
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulas');
    }
};
