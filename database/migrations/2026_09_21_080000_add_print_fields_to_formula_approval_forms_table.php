<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->string('proposal_number')->nullable()->after('sample_code');
            $table->json('organoleptic_data')->nullable()->after('sensory_product');
            $table->text('panel_result')->nullable()->after('organoleptic_data');
            $table->string('owner_decision')->nullable()->after('panel_result');
            $table->text('owner_decision_reason')->nullable()->after('owner_decision');
            $table->string('approval_company')->nullable()->default('PT Erhanesia Idea Cipta Karsa')->after('owner_decision_reason');
            $table->json('approval_signers')->nullable()->after('approval_company');
        });
    }

    public function down(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->dropColumn([
                'proposal_number',
                'organoleptic_data',
                'panel_result',
                'owner_decision',
                'owner_decision_reason',
                'approval_company',
                'approval_signers',
            ]);
        });
    }
};
