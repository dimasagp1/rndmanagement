<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StabilityTest;
use App\Models\StabilityTestIssue;
use App\Models\StabilityTestParameter;
use App\Models\StabilityTestSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class StabilityTestSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::where('email', 'staff@herbatech.com')->first();
        $om    = User::where('email', 'manager@herbatech.com')->first();

        $datasets = [
            'Jahe Merah Hangat' => [
                'batch_number'       => 'HBI-2608-01',
                'stability_protocol' => "Uji stabilitas dilakukan pada kondisi long term (25°C/60%RH) sesuai protokol perusahaan.\nTitik uji: 0, 3, 6, 9, 12 bulan.",
                'storage_condition'  => 'Long Term (25°C/60%RH)',
                'approval_status'    => 'Pending Protokol',
                'submitted_at'       => now(),
                'schedules'          => [
                    ['timepoint' => 'Bulan 0', 'due_date' => now()->subMonths(2), 'status' => 'Completed', 'tested_at' => now()->subMonths(2), 'parameters' => [
                        ['parameter' => 'Kadar Air', 'specification' => '≤ 5%', 'unit' => '%', 'result' => '3.2', 'result_status' => 'Sesuai'],
                        ['parameter' => 'Organoleptik', 'specification' => 'Normal', 'unit' => null, 'result' => 'Normal', 'result_status' => 'Sesuai'],
                    ]],
                    ['timepoint' => 'Bulan 3', 'due_date' => now()->addMonth(), 'status' => 'Pending'],
                ],
            ],
            'Kunyit Asam Segar' => [
                'batch_number'       => 'HBI-2608-02',
                'stability_protocol' => "Uji stabilitas dipercepat (Accelerated 40°C/75%RH).\nTitik uji: 0, 1, 3, 6 bulan.",
                'storage_condition'  => 'Accelerated (40°C/75%RH)',
                'approval_status'    => 'Protokol Approved',
                'submitted_at'       => now()->subDays(5),
                'approved_by_om'     => $om?->id,
                'approved_at_om'     => now()->subDays(3),
                'schedules'          => [
                    ['timepoint' => 'Bulan 0', 'due_date' => now()->subMonth(), 'status' => 'Completed', 'tested_at' => now()->subMonth(), 'parameters' => [
                        ['parameter' => 'Kadar Air', 'specification' => '≤ 5%', 'unit' => '%', 'result' => '3.8', 'result_status' => 'Sesuai'],
                    ]],
                    ['timepoint' => 'Bulan 1', 'due_date' => now()->addWeeks(2), 'status' => 'Pending'],
                    ['timepoint' => 'Bulan 3', 'due_date' => now()->addMonths(2), 'status' => 'Pending'],
                ],
                'issues'             => [
                    ['issue_type' => 'Deviasi', 'description' => 'Keterlambatan pengujian Bulan 1 karena alat ukur sedang dikalibrasi.', 'status' => 'Investigating'],
                ],
            ],
        ];

        foreach ($datasets as $productName => $data) {
            $product = Product::where('name', $productName)->first();

            if (! $product) {
                continue;
            }

            $schedules = $data['schedules'] ?? [];
            $issues    = $data['issues'] ?? [];
            unset($data['schedules'], $data['issues']);

            $test = StabilityTest::updateOrCreate(
                ['product_id' => $product->id],
                [
                    ...$data,
                    'product_name' => $product->name,
                    'created_by'   => $staff?->id,
                ]
            );

            foreach ($schedules as $index => $scheduleData) {
                $parameters = $scheduleData['parameters'] ?? [];
                unset($scheduleData['parameters']);

                $schedule = StabilityTestSchedule::updateOrCreate(
                    ['stability_test_id' => $test->id, 'timepoint' => $scheduleData['timepoint']],
                    [
                        ...$scheduleData,
                        'created_by' => $staff?->id,
                    ]
                );

                foreach ($parameters as $parameterData) {
                    StabilityTestParameter::updateOrCreate(
                        ['schedule_id' => $schedule->id, 'parameter' => $parameterData['parameter']],
                        $parameterData
                    );
                }
            }

            foreach ($issues as $issueData) {
                StabilityTestIssue::updateOrCreate(
                    ['stability_test_id' => $test->id, 'description' => $issueData['description']],
                    [
                        ...$issueData,
                        'created_by' => $staff?->id,
                    ]
                );
            }
        }
    }
}