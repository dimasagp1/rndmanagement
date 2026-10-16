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
        Schema::create('procurement_mapping_aliases', function (Blueprint $table) {
            $table->id();
            $table->enum('source_type', ['material', 'supplier'])->index();
            $table->string('source_name', 255)->index();
            $table->unsignedBigInteger('target_id');
            $table->timestamps();

            $table->unique(['source_type', 'source_name'], 'proc_alias_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_mapping_aliases');
    }
};
