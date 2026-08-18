<?php

namespace Database\Seeders;

use App\Models\FormulaApprovalForm;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class FormulaApprovalFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $om = User::where('email', 'manager@herbatech.com')->first();

        $forms = [
            'Jahe Merah Hangat' => [
                'kategori'        => 'Minuman',
                'komoditi'        => 'Rempah',
                'bentuk_sediaan'  => 'Serbuk Instan',
                'manufactured'    => 'PT Herbatech Indonesia',
                'distributor'     => 'PT Herbatech Indonesia',
                'klaim_product'   => 'Menghangatkan tubuh dan membantu meredakan masuk angin',
                'komposisi'       => 'Jahe merah, gula aren, kayu manis',
                'aturan_pakai'    => '1 sachet dicampur 150ml air hangat, 2x sehari',
                'ukuran_kemasan'  => '10 sachet x 15 gr',
                'packaging'       => 'Sachet aluminium foil + box',
                'sensory_product' => 'Pedas manis khas jahe merah',
                'target_launch'   => '2026-10-01',
                'approval_status' => 'Pending',
                'submitted_at'    => now(),
                'created_at'      => now(),
            ],
            'Kunyit Asam Segar' => [
                'kategori'        => 'Minuman',
                'komoditi'        => 'Rempah',
                'bentuk_sediaan'  => 'Serbuk Instan',
                'manufactured'    => 'PT Herbatech Indonesia',
                'distributor'     => 'PT Herbatech Indonesia',
                'klaim_product'   => 'Menyegarkan tubuh dan membantu melancarkan pencernaan',
                'komposisi'       => 'Kunyit, asam jawa, gula aren',
                'aturan_pakai'    => '1 sachet dicampur 150ml air, 2x sehari',
                'ukuran_kemasan'  => '10 sachet x 15 gr',
                'packaging'       => 'Sachet aluminium foil',
                'sensory_product' => 'Segar asam khas kunyit',
                'target_launch'   => '2026-11-01',
                'approval_status' => 'Approval by OM',
                'submitted_at'    => now()->subDays(2),
                'approved_by_om'  => $om?->id,
                'approved_at_om'  => now()->subDay(),
                'created_at'      => now()->subDays(2),
            ],
        ];

        foreach ($forms as $productName => $data) {
            $product = Product::where('name', $productName)->first();

            if (! $product) {
                continue;
            }

            FormulaApprovalForm::updateOrCreate(
                ['product_id' => $product->id],
                [
                    ...$data,
                    'product_name' => $product->name,
                ]
            );
        }
    }
}