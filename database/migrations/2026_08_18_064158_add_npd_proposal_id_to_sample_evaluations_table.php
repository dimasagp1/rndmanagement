<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_evaluations', function (Blueprint $table) {
            $table->foreignId('npd_proposal_id')->nullable()->after('product_name')
                ->constrained('npd_proposals')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sample_evaluations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('npd_proposal_id');
        });
    }
};