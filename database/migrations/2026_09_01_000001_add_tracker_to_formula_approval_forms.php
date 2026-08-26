<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->string('tracker_status')->nullable()->after('approval_status');
            $table->json('tracker_history')->nullable()->after('tracker_status');
            $table->foreignId('tracker_updated_by')->nullable()->after('tracker_history')->constrained('users')->nullOnDelete();
            $table->timestamp('tracker_updated_at')->nullable()->after('tracker_updated_by');
        });
    }

    public function down(): void
    {
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tracker_updated_by');
            $table->dropColumn(['tracker_status', 'tracker_history', 'tracker_updated_at']);
        });
    }
};
