<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stability_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
            $table->string('product_name');
            $table->string('batch_number');
            $table->text('stability_protocol')->nullable();
            $table->enum('storage_condition', ['Long Term (25°C/60%RH)', 'Intermediate (30°C/65%RH)', 'Accelerated (40°C/75%RH)', 'Khusus'])->default('Long Term (25°C/60%RH)');
            $table->text('stability_conclusion')->nullable();
            $table->enum('approval_status', ['Draft', 'Pending Protokol', 'Protokol Approved', 'Pending Laporan', 'Approved', 'Rejected'])->default('Draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('report_submitted_at')->nullable();
            $table->foreignId('approved_by_om')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at_om')->nullable();
            $table->foreignId('approved_by_gm')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at_gm')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stability_test_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stability_test_id')->constrained()->cascadeOnDelete();
            $table->string('timepoint');
            $table->date('due_date');
            $table->enum('status', ['Pending', 'Completed', 'OOS'])->default('Pending');
            $table->timestamp('tested_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stability_test_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('stability_test_schedules')->cascadeOnDelete();
            $table->string('parameter');
            $table->string('specification')->nullable();
            $table->string('unit')->nullable();
            $table->string('result')->nullable();
            $table->enum('result_status', ['Sesuai', 'Tidak Sesuai'])->nullable();
            $table->timestamps();
        });

        Schema::create('stability_test_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stability_test_id')->constrained()->cascadeOnDelete();
            $table->enum('issue_type', ['OOS', 'Deviasi']);
            $table->text('description');
            $table->enum('status', ['Open', 'Investigating', 'Closed'])->default('Open');
            $table->text('resolution')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stability_test_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stability_test_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['Protokol Stabilitas', 'Laporan Hasil Stabilitas', 'Lainnya']);
            $table->string('file_path');
            $table->string('original_name');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stability_test_attachments');
        Schema::dropIfExists('stability_test_issues');
        Schema::dropIfExists('stability_test_parameters');
        Schema::dropIfExists('stability_test_schedules');
        Schema::dropIfExists('stability_tests');
    }
};
