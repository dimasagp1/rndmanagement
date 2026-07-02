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
        Schema::table('formulas', function (Blueprint $table) {
            $table->string('formula_type')->nullable()->after('name');
            $table->date('formula_date')->nullable()->after('formula_type');
            $table->text('preparation_method')->nullable()->after('development_stage');
            $table->text('notes')->nullable()->after('preparation_method');
            $table->string('result')->nullable()->after('notes');
        });

        Schema::table('formula_materials', function (Blueprint $table) {
            $table->decimal('price_per_kg', 12, 2)->nullable()->after('percentage');
            $table->decimal('price_per_gram', 10, 4)->nullable()->after('price_per_kg');
            $table->decimal('dose_2g', 10, 4)->nullable()->after('price_per_gram');
            $table->decimal('dose_05g', 10, 4)->nullable()->after('dose_2g');
            $table->decimal('sachet_30', 10, 2)->nullable()->after('dose_05g');
            $table->decimal('hpp_rm', 12, 2)->nullable()->after('sachet_30');
        });
    }

    public function down(): void
    {
        Schema::table('formulas', function (Blueprint $table) {
            $table->dropColumn(['formula_type', 'formula_date', 'preparation_method', 'notes', 'result']);
        });

        Schema::table('formula_materials', function (Blueprint $table) {
            $table->dropColumn(['price_per_kg', 'price_per_gram', 'dose_2g', 'dose_05g', 'sachet_30', 'hpp_rm']);
        });
    }
};
