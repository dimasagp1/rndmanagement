<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PRF tidak lagi memerlukan approval OM/GM:
        // 1) perluas enum dulu agar 'Submitted' dapat disimpan
        // 2) mapping data lama ke status baru ('Rejected' -> Draft)
        // 3) persempit enum menjadi hanya Draft/Submitted
        Schema::table('prfs', function (Blueprint $table) {
            $table->enum('approval_status', ['Draft', 'Pending Tahap 1', 'Approval by OM', 'Completed by GM', 'Rejected', 'Submitted'])->default('Draft')->change();
        });

        DB::table('prfs')
            ->whereIn('approval_status', ['Pending Tahap 1', 'Approval by OM', 'Completed by GM'])
            ->update(['approval_status' => 'Submitted']);

        DB::table('prfs')
            ->where('approval_status', 'Rejected')
            ->update(['approval_status' => 'Draft']);

        Schema::table('prfs', function (Blueprint $table) {
            $table->enum('approval_status', ['Draft', 'Submitted'])->default('Draft')->change();
        });

        Schema::create('prf_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prf_id')->constrained('prfs')->onDelete('cascade');
            $table->string('document_type')->default('Lainnya');
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prf_documents');

        DB::table('prfs')
            ->where('approval_status', 'Submitted')
            ->update(['approval_status' => 'Completed by GM']);

        Schema::table('prfs', function (Blueprint $table) {
            $table->enum('approval_status', ['Draft', 'Pending Tahap 1', 'Pending Tahap 2', 'Approved', 'Rejected'])->default('Draft')->change();
        });
    }
};