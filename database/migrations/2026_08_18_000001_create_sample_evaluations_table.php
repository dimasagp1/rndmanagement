<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('sample_id')->unique(); // Sample ID
            $table->string('product_name'); // Nama Produk
            $table->foreignId('project_owner_id')->constrained('users')->onDelete('cascade'); // Project Owner
            $table->enum('status', ['In Progress', 'Approved', 'Reform'])->default('In Progress');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_evaluations');
    }
};