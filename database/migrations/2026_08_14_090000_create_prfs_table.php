<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prfs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('requestor');
            $table->string('department');
            $table->text('product_concept');
            $table->string('target_market')->nullable();
            $table->string('product_category')->nullable();
            $table->date('target_launch')->nullable();
            $table->string('product_name')->nullable();
            $table->enum('approval_status', ['Draft', 'Pending Tahap 1', 'Pending Tahap 2', 'Approved', 'Rejected'])->default('Draft');
            $table->foreignId('approved_by_om')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by_gm')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prfs');
    }
};
