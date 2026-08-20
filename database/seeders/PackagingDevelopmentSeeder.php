<?php

namespace Database\Seeders;

use App\Models\PackagingCompatibilityEvaluation;
use App\Models\PackagingCompatibilityParameter;
use App\Models\PackagingDevelopment;
use App\Models\PackagingMaterialDevelopment;
use App\Models\PackagingPrimary;
use App\Models\PackagingSecondary;
use App\Models\PackagingSpecification;
use App\Models\PackagingSupplier;
use App\Models\PackagingTrial;
use App\Models\PackagingTrialParameter;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class PackagingDevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::where('email', 'staff@herbatech.com')->first();
        $om    = User::where('email', 'manager@herbatech.com')->first();
        $gm    = User::where('email', 'lisa@herbatech.com')->first();

        $datasets = [
            'Jahe Merah Hangat' => [
                'product_category'      => 'Serbuk',
                'packaging_type'        => 'Sachet',
                'development_purpose'   => 'New Product Development',
                'target_launch'         => now()->addMonths(2)->format('Y-m-d'),
                'target_market'         => 'Semua usia',
                'approval_status'       => 'Pending OM',
                'development_stage'     => 'In Review',
                'submitted_at'          => now()->subDays(2),
                'specification'         => [
                    'specification_no'   => 'PS-2026-001',
                    'packaging_type'     => 'Sachet',
                    'dimension'          => '100 × 150 mm',
                    'nominal_weight'     => '2 g',
                    'tolerance'          => '± 5%',
                    'material_structure' => 'PET/AL/PE',
                    'thickness'          => '100 micron',
                    'color'              => 'White',
                    'printing'           => '4 Color',
                    'sealing_type'       => 'Heat Seal',
                    'shelf_life'         => '24 Months',
                    'storage_condition'  => 'Room Temperature',
                    'reference'          => 'Internal Standard',
                ],
                'primary' => [
                    'packaging_type'      => 'Sachet',
                    'material'            => 'PET/AL/PE',
                    'supplier_name'       => 'PT ABC Packaging',
                    'dimension'           => '100 × 150 mm',
                    'thickness'           => '100 micron',
                    'product_contact'     => 'Yes',
                    'barrier_requirement' => 'High',
                    'light_protection'    => 'Yes',
                    'moisture_protection' => 'Yes',
                    'oxygen_protection'   => 'Yes',
                    'seal_requirement'    => 'Heat Seal',
                ],
                'secondary' => [
                    'packaging_type'   => 'Folding Box',
                    'material'         => 'Duplex 350 gsm',
                    'dimension'        => '120 × 80 × 50 mm',
                    'printing'         => 'Full Color',
                    'finishing'        => 'Matte Lamination',
                    'quantity_per_box' => '10 Sachets',
                    'supplier_name'    => 'PT XYZ Printing',
                ],
                'materials' => [
                    [
                        'material_name'          => 'PET/AL/PE',
                        'material_type'          => 'Laminated Film',
                        'current_material'       => 'PET/PE',
                        'proposed_material'      => 'PET/AL/PE',
                        'material_specification' => 'Internal Specification',
                        'reason_for_change'      => 'Meningkatkan barrier moisture dan oxygen.',
                        'expected_benefit'       => 'Meningkatkan product stability dan shelf life.',
                        'risk'                   => 'Low',
                    ],
                ],
                'suppliers' => [
                    [
                        'supplier_name'        => 'PT ABC Packaging',
                        'supplier_code'        => 'SUP-001',
                        'material'             => 'PET/AL/PE',
                        'contact_person'       => 'Budi',
                        'qualification_status' => 'Qualified',
                        'certificate'          => 'ISO 9001',
                        'audit_status'         => 'Passed',
                        'approval_date'        => now()->subMonths(3)->format('Y-m-d'),
                    ],
                ],
                'trials' => [
                    [
                        'trial_date'         => now()->subWeeks(2)->format('Y-m-d'),
                        'trial_batch'        => 'BATCH-001',
                        'packaging_material' => 'PET/AL/PE',
                        'machine'            => 'Packing Machine 01',
                        'quantity'           => '10,000 pcs',
                        'operator'           => 'Production Team',
                        'trial_purpose'      => 'Seal Optimization',
                        'result'             => 'Pass',
                        'parameters'         => [
                            ['parameter' => 'Sealing Temperature', 'target' => '160°C', 'actual' => '158°C', 'result' => 'Pass'],
                            ['parameter' => 'Sealing Pressure', 'target' => '3 bar', 'actual' => '3 bar', 'result' => 'Pass'],
                            ['parameter' => 'Leak Test', 'target' => 'Pass', 'actual' => 'Pass', 'result' => 'Pass'],
                        ],
                    ],
                ],
                'compatibilities' => [
                    [
                        'evaluation_date'   => now()->subWeek()->format('Y-m-d'),
                        'evaluation_method' => 'Internal Test Method',
                        'test_condition'    => 'Room Temperature',
                        'test_duration'     => '30 Days',
                        'evaluator'         => 'QC',
                        'result'            => 'Pass',
                        'conclusion'        => 'Compatible',
                        'parameters'        => [
                            ['parameter' => 'Appearance', 'result' => 'Pass'],
                            ['parameter' => 'Odor', 'result' => 'Pass'],
                            ['parameter' => 'Moisture Protection', 'result' => 'Pass'],
                            ['parameter' => 'Seal Integrity', 'result' => 'Pass'],
                        ],
                    ],
                ],
            ],
            'Kunyit Asam Segar' => [
                'product_category'      => 'COD',
                'packaging_type'        => 'Bottle',
                'development_purpose'   => 'Packaging Improvement',
                'target_launch'         => now()->addMonth()->format('Y-m-d'),
                'target_market'         => 'Semua usia',
                'approval_status'       => 'Pending GM',
                'development_stage'     => 'In Review',
                'submitted_at'          => now()->subDays(6),
                'approved_by_om'        => $om?->id,
                'approved_at_om'        => now()->subDays(3),
                'specification'         => [
                    'specification_no'   => 'PS-2026-002',
                    'packaging_type'     => 'Bottle',
                    'dimension'           => '50 × 120 mm',
                    'nominal_weight'      => '100 ml',
                    'tolerance'           => '± 3%',
                    'material_structure'  => 'HDPE',
                    'thickness'           => '1.5 mm',
                    'color'               => 'Amber',
                    'printing'            => '1 Color',
                    'sealing_type'        => 'Induction Seal',
                    'shelf_life'          => '18 Months',
                    'storage_condition'   => 'Room Temperature',
                    'reference'           => 'Internal Standard',
                ],
                'primary' => [
                    'packaging_type'      => 'Bottle',
                    'material'            => 'HDPE',
                    'supplier_name'       => 'PT Plastik Nusantara',
                    'dimension'           => '50 × 120 mm',
                    'thickness'           => '1.5 mm',
                    'product_contact'     => 'Yes',
                    'barrier_requirement' => 'Medium',
                    'light_protection'    => 'Yes',
                    'moisture_protection' => 'No',
                    'oxygen_protection'   => 'No',
                    'seal_requirement'    => 'Induction Seal',
                ],
                'suppliers' => [
                    [
                        'supplier_name'        => 'PT Plastik Nusantara',
                        'supplier_code'        => 'SUP-002',
                        'material'             => 'HDPE',
                        'contact_person'       => 'Dewi',
                        'qualification_status' => 'Qualified',
                        'certificate'          => 'ISO 9001',
                        'audit_status'         => 'Passed',
                        'approval_date'        => now()->subMonths(6)->format('Y-m-d'),
                    ],
                ],
                'trials' => [
                    [
                        'trial_date'         => now()->subWeeks(3)->format('Y-m-d'),
                        'trial_batch'        => 'BATCH-002',
                        'packaging_material' => 'HDPE',
                        'machine'            => 'Filling Line 02',
                        'quantity'           => '5,000 pcs',
                        'operator'           => 'Production Team',
                        'trial_purpose'      => 'Capping Speed Optimization',
                        'result'             => 'Pass',
                        'parameters'         => [
                            ['parameter' => 'Torque Test', 'target' => '8-12 kgf.cm', 'actual' => '10 kgf.cm', 'result' => 'Pass'],
                            ['parameter' => 'Leak Test', 'target' => 'Pass', 'actual' => 'Pass', 'result' => 'Pass'],
                        ],
                    ],
                ],
            ],
            'Sari Kurma Madu' => [
                'product_category'      => 'COD',
                'packaging_type'        => 'Pouch',
                'development_purpose'   => 'Material Change',
                'target_launch'         => now()->subMonth()->format('Y-m-d'),
                'target_market'         => 'Semua usia',
                'approval_status'       => 'Approved',
                'development_stage'     => 'Approved',
                'submitted_at'          => now()->subMonths(2),
                'approved_by_om'        => $om?->id,
                'approved_at_om'        => now()->subMonths(2)->addDays(2),
                'approved_by_gm'        => $gm?->id,
                'approved_at_gm'        => now()->subMonths(2)->addDays(4),
                'specification'         => [
                    'specification_no'   => 'PS-2026-003',
                    'packaging_type'     => 'Pouch',
                    'dimension'          => '90 × 140 mm',
                    'nominal_weight'     => '30 ml',
                    'tolerance'          => '± 5%',
                    'material_structure' => 'PET/AL/PE',
                    'thickness'          => '90 micron',
                    'color'              => 'Clear',
                    'printing'           => '4 Color',
                    'sealing_type'       => 'Heat Seal',
                    'shelf_life'         => '12 Months',
                    'storage_condition'  => 'Room Temperature',
                    'reference'          => 'Internal Standard',
                ],
                'primary' => [
                    'packaging_type'      => 'Pouch',
                    'material'            => 'PET/AL/PE',
                    'supplier_name'       => 'PT ABC Packaging',
                    'dimension'           => '90 × 140 mm',
                    'thickness'           => '90 micron',
                    'product_contact'     => 'Yes',
                    'barrier_requirement' => 'High',
                    'light_protection'    => 'Yes',
                    'moisture_protection' => 'Yes',
                    'oxygen_protection'   => 'Yes',
                    'seal_requirement'    => 'Heat Seal',
                ],
                'secondary' => [
                    'packaging_type'   => 'Carton',
                    'material'         => 'Kraft 300 gsm',
                    'dimension'        => '300 × 200 × 150 mm',
                    'printing'         => '1 Color',
                    'finishing'        => 'None',
                    'quantity_per_box' => '100 Pouches',
                    'supplier_name'    => 'PT XYZ Printing',
                ],
                'materials' => [
                    [
                        'material_name'          => 'PET/AL/PE (90 micron)',
                        'material_type'          => 'Laminated Film',
                        'current_material'       => 'PET/PE (70 micron)',
                        'proposed_material'      => 'PET/AL/PE (90 micron)',
                        'material_specification' => 'Internal Specification',
                        'reason_for_change'      => 'Meningkatkan barrier moisture dan oxygen.',
                        'expected_benefit'       => 'Meningkatkan product stability dan shelf life.',
                        'risk'                   => 'Low',
                        'status'                 => 'Approved',
                    ],
                ],
                'suppliers' => [
                    [
                        'supplier_name'        => 'PT ABC Packaging',
                        'supplier_code'        => 'SUP-001',
                        'material'             => 'PET/AL/PE',
                        'contact_person'       => 'Budi',
                        'qualification_status' => 'Qualified',
                        'certificate'          => 'ISO 9001',
                        'audit_status'         => 'Passed',
                        'approval_date'        => now()->subMonths(8)->format('Y-m-d'),
                    ],
                ],
                'trials' => [
                    [
                        'trial_date'         => now()->subMonths(3)->format('Y-m-d'),
                        'trial_batch'        => 'BATCH-003',
                        'packaging_material' => 'PET/AL/PE',
                        'machine'            => 'Pouch Filling Machine 03',
                        'quantity'           => '20,000 pcs',
                        'operator'           => 'Production Team',
                        'trial_purpose'      => 'Leak Test & Seal Optimization',
                        'result'             => 'Pass',
                        'parameters'         => [
                            ['parameter' => 'Sealing Temperature', 'target' => '165°C', 'actual' => '164°C', 'result' => 'Pass'],
                            ['parameter' => 'Leak Test', 'target' => 'Pass', 'actual' => 'Pass', 'result' => 'Pass'],
                        ],
                    ],
                ],
                'compatibilities' => [
                    [
                        'evaluation_date'   => now()->subMonths(2)->addDays(1)->format('Y-m-d'),
                        'evaluation_method' => 'Internal Test Method',
                        'test_condition'    => 'Accelerated 40°C/75%RH',
                        'test_duration'     => '60 Days',
                        'evaluator'         => 'QC',
                        'result'            => 'Pass',
                        'conclusion'        => 'Compatible',
                        'parameters'        => [
                            ['parameter' => 'Appearance', 'result' => 'Pass'],
                            ['parameter' => 'Odor', 'result' => 'Pass'],
                            ['parameter' => 'Stability', 'result' => 'Pass'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($datasets as $productName => $data) {
            $product = Product::where('name', $productName)->first();

            if (! $product) {
                continue;
            }

            $specification    = $data['specification'] ?? null;
            $primary          = $data['primary'] ?? null;
            $secondary        = $data['secondary'] ?? null;
            $materials        = $data['materials'] ?? [];
            $suppliers        = $data['suppliers'] ?? [];
            $trials           = $data['trials'] ?? [];
            $compatibilities  = $data['compatibilities'] ?? [];
            unset($data['specification'], $data['primary'], $data['secondary'], $data['materials'], $data['suppliers'], $data['trials'], $data['compatibilities']);

            $development = PackagingDevelopment::updateOrCreate(
                ['product_id' => $product->id],
                [
                    ...$data,
                    'product_name' => $product->name,
                    'created_by'   => $staff?->id,
                ]
            );

            if ($specification) {
                PackagingSpecification::updateOrCreate(
                    ['packaging_development_id' => $development->id],
                    $specification
                );
            }

            if ($primary) {
                PackagingPrimary::updateOrCreate(
                    ['packaging_development_id' => $development->id],
                    $primary
                );
            }

            if ($secondary) {
                PackagingSecondary::updateOrCreate(
                    ['packaging_development_id' => $development->id],
                    $secondary
                );
            }

            foreach ($materials as $materialData) {
                PackagingMaterialDevelopment::updateOrCreate(
                    ['packaging_development_id' => $development->id, 'material_name' => $materialData['material_name']],
                    $materialData
                );
            }

            foreach ($suppliers as $supplierData) {
                PackagingSupplier::updateOrCreate(
                    ['packaging_development_id' => $development->id, 'supplier_name' => $supplierData['supplier_name']],
                    $supplierData
                );
            }

            foreach ($trials as $trialIndex => $trialData) {
                $parameters = $trialData['parameters'] ?? [];
                unset($trialData['parameters']);

                $trial = PackagingTrial::updateOrCreate(
                    ['packaging_development_id' => $development->id, 'trial_batch' => $trialData['trial_batch']],
                    $trialData
                );

                foreach ($parameters as $parameterData) {
                    PackagingTrialParameter::updateOrCreate(
                        ['packaging_trial_id' => $trial->id, 'parameter' => $parameterData['parameter']],
                        $parameterData
                    );
                }
            }

            foreach ($compatibilities as $compatIndex => $compatData) {
                $parameters = $compatData['parameters'] ?? [];
                unset($compatData['parameters']);

                $evaluation = PackagingCompatibilityEvaluation::updateOrCreate(
                    ['packaging_development_id' => $development->id, 'evaluation_date' => $compatData['evaluation_date']],
                    $compatData
                );

                foreach ($parameters as $parameterData) {
                    PackagingCompatibilityParameter::updateOrCreate(
                        ['packaging_compatibility_id' => $evaluation->id, 'parameter' => $parameterData['parameter']],
                        $parameterData
                    );
                }
            }

            if ($development->approval_status === 'Pending OM') {
                $development->approvals()->updateOrCreate(['step' => 'OM Approval'], ['status' => 'Pending', 'approver_id' => null, 'approved_at' => null]);
            }

            if ($development->approval_status === 'Pending GM') {
                $development->approvals()->updateOrCreate(
                    ['step' => 'OM Approval'],
                    ['status' => 'Approved', 'approver_id' => $om?->id, 'comment' => 'Spesifikasi sesuai.', 'approved_at' => $development->approved_at_om]
                );
                $development->approvals()->updateOrCreate(['step' => 'GM Approval'], ['status' => 'Pending', 'approver_id' => null, 'approved_at' => null]);
            }

            if ($development->approval_status === 'Approved') {
                $development->approvals()->updateOrCreate(
                    ['step' => 'OM Approval'],
                    ['status' => 'Approved', 'approver_id' => $om?->id, 'comment' => 'Spesifikasi sesuai.', 'approved_at' => $development->approved_at_om]
                );
                $development->approvals()->updateOrCreate(
                    ['step' => 'GM Approval'],
                    ['status' => 'Approved', 'approver_id' => $gm?->id, 'comment' => 'Disetujui final.', 'approved_at' => $development->approved_at_gm]
                );
                $development->revisions()->updateOrCreate(
                    ['packaging_development_id' => $development->id, 'revision' => 'Rev 01'],
                    ['change_description' => 'Disetujui final oleh GM.', 'changed_by' => $gm?->id]
                );
            }

            $development->auditLogs()->create([
                'user_id' => $staff?->id,
                'action'  => 'Membuat Packaging Development',
                'details' => "Dokumen {$development->code} dibuat sebagai Draft.",
            ]);
        }
    }
}