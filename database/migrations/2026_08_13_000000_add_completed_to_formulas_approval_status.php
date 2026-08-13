<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formulas', function (Blueprint $table) {
            $table->enum('approval_status', [
                'Draft', 'Pending Tahap 1', 'Pending Tahap 2', 'Approved', 'Rejected', 'Completed',
            ])->default('Draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('formulas', function (Blueprint $table) {
            $table->enum('approval_status', [
                'Draft', 'Pending Tahap 1', 'Pending Tahap 2', 'Approved', 'Rejected',
            ])->default('Draft')->change();
        });
    }
};
