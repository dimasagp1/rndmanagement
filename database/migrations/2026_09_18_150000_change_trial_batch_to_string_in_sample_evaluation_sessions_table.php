<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_evaluation_sessions', function (Blueprint $table) {
            $table->string('trial_batch', 100)->change();
        });
    }

    public function down(): void
    {
        Schema::table('sample_evaluation_sessions', function (Blueprint $table) {
            $table->unsignedInteger('trial_batch')->change();
        });
    }
};
