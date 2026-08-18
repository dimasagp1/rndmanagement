<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. QTPP — Quality Target Product Profile (satu per study)
        Schema::create('preformulation_study_qtpps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->unique()->constrained('preformulation_studies')->onDelete('cascade');
            $table->string('product_category')->nullable();
            $table->string('dosage_form')->nullable();
            $table->string('target_market')->nullable();
            $table->date('target_launch')->nullable();
            $table->timestamps();
        });

        // 2. QTPP Attributes (banyak per QTPP)
        Schema::create('preformulation_study_qtpp_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qtpp_id')->constrained('preformulation_study_qtpps')->onDelete('cascade');
            $table->string('quality_attribute');
            $table->text('target');
            $table->string('unit')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        // 3. CQA — Critical Quality Attributes
        Schema::create('preformulation_study_cqas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained('preformulation_studies')->onDelete('cascade');
            $table->string('quality_attribute');
            $table->text('target');
            $table->enum('is_cqa', ['Y', 'N'])->default('Y');
            $table->enum('criticality', ['Critical', 'Major', 'Minor'])->default('Minor');
            $table->text('justification')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        // 4. CMA — Critical Material Attributes
        Schema::create('preformulation_study_cmas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained('preformulation_studies')->onDelete('cascade');
            $table->string('material');
            $table->string('material_attribute');
            $table->text('target');
            $table->string('unit')->nullable();
            $table->json('cqa_ids')->nullable();
            $table->enum('criticality', ['Critical', 'Major', 'Minor'])->default('Minor');
            $table->text('justification')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        // 5. CPP — Critical Process Parameters
        Schema::create('preformulation_study_cpps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained('preformulation_studies')->onDelete('cascade');
            $table->string('process_step');
            $table->string('parameter');
            $table->decimal('minimum', 12, 4)->nullable();
            $table->decimal('target', 12, 4)->nullable();
            $table->decimal('maximum', 12, 4)->nullable();
            $table->string('unit')->nullable();
            $table->json('cqa_ids')->nullable();
            $table->enum('criticality', ['Critical', 'Major', 'Minor'])->default('Minor');
            $table->text('justification')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        // 6. Risk Assessment (RPN)
        Schema::create('preformulation_study_risks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained('preformulation_studies')->onDelete('cascade');
            $table->string('source_type'); // CMA / CPP
            $table->string('source_name');
            $table->string('cqa_name');
            $table->unsignedTinyInteger('severity')->default(1);
            $table->unsignedTinyInteger('occurrence')->default(1);
            $table->unsignedTinyInteger('detectability')->default(1);
            $table->unsignedInteger('rpn')->default(0);
            $table->enum('risk_level', ['Low', 'Medium', 'High'])->default('Low');
            $table->timestamps();
        });

        // 7. Design Space
        Schema::create('preformulation_study_design_spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained('preformulation_studies')->onDelete('cascade');
            $table->string('parameter');
            $table->decimal('minimum', 12, 4)->nullable();
            $table->decimal('target', 12, 4)->nullable();
            $table->decimal('maximum', 12, 4)->nullable();
            $table->string('unit')->nullable();
            $table->timestamps();
        });

        // 8. Control Strategy
        Schema::create('preformulation_study_control_strategies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained('preformulation_studies')->onDelete('cascade');
            $table->string('cqa');
            $table->string('control_point');
            $table->string('specification')->nullable();
            $table->string('control_method')->nullable();
            $table->string('monitoring')->nullable();
            $table->string('frequency')->nullable();
            $table->string('responsible_department')->nullable();
            $table->string('action_oos')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preformulation_study_control_strategies');
        Schema::dropIfExists('preformulation_study_design_spaces');
        Schema::dropIfExists('preformulation_study_risks');
        Schema::dropIfExists('preformulation_study_cpps');
        Schema::dropIfExists('preformulation_study_cmas');
        Schema::dropIfExists('preformulation_study_cqas');
        Schema::dropIfExists('preformulation_study_qtpp_attributes');
        Schema::dropIfExists('preformulation_study_qtpps');
    }
};