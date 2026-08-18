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
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->enum('approval_status', ['Pending', 'Approval by OM', 'Approved', 'Rejected'])->default('Pending')->after('formula_id');
            $table->timestamp('submitted_at')->nullable()->after('approval_status');
            $table->foreignId('approved_by_om')->nullable()->constrained('users')->nullOnDelete()->after('submitted_at');
            $table->timestamp('approved_at_om')->nullable()->after('approved_by_om');
            $table->foreignId('approved_by_gm')->nullable()->constrained('users')->nullOnDelete()->after('approved_at_om');
            $table->timestamp('approved_at_gm')->nullable()->after('approved_by_gm');
            $table->text('rejection_notes')->nullable()->after('approved_at_gm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->dropForeign(['approved_by_om']);
            $table->dropForeign(['approved_by_gm']);
            $table->dropColumn(['approval_status', 'submitted_at', 'approved_by_om', 'approved_at_om', 'approved_by_gm', 'approved_at_gm', 'rejection_notes']);
        });
    }
};