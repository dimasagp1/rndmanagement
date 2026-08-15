<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prfs', function (Blueprint $table) {
            $table->dropForeign(['approved_by_om']);
            $table->dropForeign(['approved_by_gm']);
            $table->dropColumn([
                'approval_status',
                'approved_by_om',
                'approved_by_gm',
                'approved_at',
                'rejection_notes',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('prfs', function (Blueprint $table) {
            $table->enum('approval_status', ['Submitted'])->default('Submitted')->after('product_name');
            $table->foreignId('approved_by_om')->nullable()->constrained('users')->onDelete('set null')->after('approval_status');
            $table->foreignId('approved_by_gm')->nullable()->constrained('users')->onDelete('set null')->after('approved_by_om');
            $table->timestamp('approved_at')->nullable()->after('approved_by_gm');
            $table->text('rejection_notes')->nullable()->after('approved_at');
        });
    }
};