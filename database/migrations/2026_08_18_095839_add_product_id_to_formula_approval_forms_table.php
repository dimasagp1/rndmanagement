<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->nullable()
                ->after('id')
                ->constrained('products')
                ->nullOnDelete();
        });

        DB::table('formula_approval_forms')
            ->whereNull('product_id')
            ->update([
                'product_id' => DB::raw(
                    '(SELECT id FROM products WHERE products.name = formula_approval_forms.product_name LIMIT 1)'
                ),
            ]);

        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->dropForeign(['formula_id']);
            $table->dropUnique('formula_approval_forms_formula_id_unique');
            $table->dropColumn('formula_id');
            $table->unique('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->dropUnique(['product_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            $table->foreignId('formula_id')->nullable()->after('id')->constrained('formulas')->cascadeOnDelete();
        });
    }
};