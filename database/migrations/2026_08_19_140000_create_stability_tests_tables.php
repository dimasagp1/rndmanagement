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
        Schema::create('stability_tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stability_test_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stability_test_id')->constrained('stability_tests')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stability_test_attachments');
        Schema::dropIfExists('stability_tests');
    }
};