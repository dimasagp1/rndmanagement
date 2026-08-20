<?php

namespace Database\Seeders;

use App\Models\FormulaApprovalForm;
use App\Models\User;
use Illuminate\Database\Seeder;

class FormulaApprovalFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $om   = User::where('email', 'manager@herbatech.com')->first();
        $gm   = User::where('email', 'lisa@herbatech.com')->first();

        $forms = [
            [
                'product_name'    => 'Jahe Merah Hangat',
                'kategori'        => 'New Product',
                'komoditi'        => 'Rempah',
                'bentuk_sediaan'  => 'Serbuk',
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
            [
                'product_name'    => 'Kunyit Asam Segar',
                'kategori'        => 'Existing Product',
                'komoditi'        => 'Rempah',
                'bentuk_sediaan'  => 'Serbuk',
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
            [
                'product_name'    => 'Teh Rosella Premium',
                'kategori'        => 'New Product',
                'komoditi'        => 'Bunga',
                'bentuk_sediaan'  => 'Kapsul',
                'manufactured'    => 'PT Herbatech Indonesia',
                'distributor'     => 'PT Distribusi Sehat',
                'klaim_product'   => 'Membantu menjaga daya tahan tubuh',
                'komposisi'       => 'Ekstrak kelopak rosella, maltodekstrin',
                'aturan_pakai'    => '1 kapsul, 2x sehari setelah makan',
                'ukuran_kemasan'  => '60 kapsul x 500 mg',
                'packaging'       => 'Botol HDPE + box',
                'sensory_product' => 'Asam segar khas rosella',
                'target_launch'   => '2026-12-01',
                'approval_status' => 'Approved',
                'submitted_at'    => now()->subDays(6),
                'approved_by_om'  => $om?->id,
                'approved_at_om'  => now()->subDays(4),
                'approved_by_gm'  => $gm?->id,
                'approved_at_gm'  => now()->subDays(2),
                'created_at'      => now()->subDays(6),
            ],
            [
                'product_name'    => 'Susu Kedelai Bubuk',
                'kategori'        => 'Existing Product',
                'komoditi'        => 'Serealia',
                'bentuk_sediaan'  => 'Serbuk',
                'manufactured'    => 'PT Herbatech Indonesia',
                'distributor'     => 'PT Herbatech Indonesia',
                'klaim_product'   => 'Sumber protein nabati untuk keluarga',
                'komposisi'       => 'Kedelai hitam, gula aren, vanili',
                'aturan_pakai'    => '3 sdm dicampur 200ml air hangat, 2x sehari',
                'ukuran_kemasan'  => '20 sachet x 25 gr',
                'packaging'       => 'Sachet aluminium foil',
                'sensory_product' => 'Gurih manis khas kedelai',
                'target_launch'   => '2027-01-01',
                'approval_status' => 'Rejected',
                'rejection_notes' => 'Komposisi kedelai belum memenuhi standar mutu protein. Silakan revisi dan ajukan ulang.',
                'submitted_at'    => now()->subDays(8),
                'created_at'      => now()->subDays(8),
            ],
        ];

        foreach ($forms as $data) {
            FormulaApprovalForm::updateOrCreate(
                ['product_name' => $data['product_name']],
                [
                    ...$data,
                    'product_id' => null,
                ]
            );
        }
    }
}