<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Formula;
use App\Models\FormulaMaterial;
use App\Models\TrialRm;
use App\Models\TrialRmVerification;
use App\Models\TrialPm;
use App\Models\TrialPmApproval;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $staff   = User::where('email', 'staff@herbatech.com')->first();
        $siti    = User::where('email', 'siti@herbatech.com')->first();
        $manager = User::where('email', 'manager@herbatech.com')->first();
        $gm      = User::where('email', 'lisa@herbatech.com')->first();

        $mats = Material::all()->keyBy('name');
        $sups = Supplier::all();
        $sup1 = $sups->where('name', 'PT Alam Herbal Indonesia')->first();
        $sup2 = $sups->where('name', 'CV Sehat Alami')->first();
        $sup3 = $sups->where('name', 'PT Madu Hutan Nusantara')->first();
        $sup4 = $sups->where('name', 'CV Rempah Tradisional')->first();
        $sup5 = $sups->where('name', 'PT Herba Medika')->first();

        // ──────────────────────────────────────────────────
        // FORMULA 1: Jahe Merah Hangat V1 — APPROVED
        // ──────────────────────────────────────────────────
        $f1 = Formula::create([
            'code'              => 'FRM-202606-001',
            'name'              => 'Jahe Merah Hangat',
            'version'           => 1,
            'development_stage' => 'Final',
            'approval_status'   => 'Approved',
            'created_by'        => $staff->id,
            'approved_by_om'    => $manager->id,
            'approved_by_gm'    => $gm->id,
            'approved_at'       => Carbon::now()->subDays(10),
            'created_at'        => Carbon::now()->subDays(20),
            'updated_at'        => Carbon::now()->subDays(10),
        ]);
        $this->addMaterials($f1, [
            [$mats['Ekstrak Jahe']->id,        $sup1->id, 35],
            [$mats['Madu Murni']->id,           $sup3->id, 25],
            [$mats['Kayu Manis Bubuk']->id,     $sup4->id, 10],
            [$mats['Gula Aren']->id,            $sup2->id, 20],
            [$mats['Air Mineral']->id,          $sup5->id, 10],
        ]);

        // ──────────────────────────────────────────────────
        // FORMULA 2: Kunyit Asam Segar V1 — APPROVED
        // ──────────────────────────────────────────────────
        $f2 = Formula::create([
            'code'              => 'FRM-202606-002',
            'name'              => 'Kunyit Asam Segar',
            'version'           => 1,
            'development_stage' => 'Final',
            'approval_status'   => 'Approved',
            'created_by'        => $siti->id,
            'approved_by_om'    => $manager->id,
            'approved_by_gm'    => $gm->id,
            'approved_at'       => Carbon::now()->subDays(5),
            'created_at'        => Carbon::now()->subDays(15),
            'updated_at'        => Carbon::now()->subDays(5),
        ]);
        $this->addMaterials($f2, [
            [$mats['Ekstrak Kunyit']->id,       $sup4->id, 30],
            [$mats['Asam Jawa']->id,            $sup2->id, 20],
            [$mats['Madu Murni']->id,           $sup3->id, 20],
            [$mats['Gula Aren']->id,            $sup2->id, 15],
            [$mats['Daun Mint Kering']->id,     $sup1->id, 5],
            [$mats['Air Mineral']->id,          $sup5->id, 10],
        ]);

        // ──────────────────────────────────────────────────
        // FORMULA 3: Temulawak Plus V1 — PENDING TAHAP 2
        // ──────────────────────────────────────────────────
        $f3 = Formula::create([
            'code'              => 'FRM-202607-001',
            'name'              => 'Temulawak Plus Imunitas',
            'version'           => 1,
            'development_stage' => 'Optimalisasi',
            'approval_status'   => 'Pending Tahap 2',
            'created_by'        => $staff->id,
            'approved_by_om'    => $manager->id,
            'created_at'        => Carbon::now()->subDays(3),
            'updated_at'        => Carbon::now()->subDays(1),
        ]);
        $this->addMaterials($f3, [
            [$mats['Temulawak Bubuk']->id,      $sup4->id, 40],
            [$mats['Ekstrak Jahe']->id,         $sup1->id, 20],
            [$mats['Madu Murni']->id,           $sup3->id, 25],
            [$mats['Gula Aren']->id,            $sup2->id, 15],
        ]);

        // ──────────────────────────────────────────────────
        // FORMULA 4: Lidah Buaya Gel V1 — PENDING TAHAP 1
        // ──────────────────────────────────────────────────
        $f4 = Formula::create([
            'code'              => 'FRM-202607-002',
            'name'              => 'Lidah Buaya Gel Herbal',
            'version'           => 1,
            'development_stage' => 'Pra-Trial',
            'approval_status'   => 'Pending Tahap 1',
            'created_by'        => $siti->id,
            'created_at'        => Carbon::now()->subHours(12),
            'updated_at'        => Carbon::now()->subHours(12),
        ]);
        $this->addMaterials($f4, [
            [$mats['Ekstrak Lidah Buaya']->id,  $sup1->id, 60],
            [$mats['Madu Murni']->id,           $sup3->id, 20],
            [$mats['Daun Mint Kering']->id,     $sup1->id, 10],
            [$mats['Air Mineral']->id,          $sup5->id, 10],
        ]);

        // ──────────────────────────────────────────────────
        // FORMULA 5: Jamu Beras Kencur V1 — DRAFT
        // ──────────────────────────────────────────────────
        $f5 = Formula::create([
            'code'              => 'FRM-202607-003',
            'name'              => 'Jamu Beras Kencur Tradisional',
            'version'           => 1,
            'development_stage' => 'Draf',
            'approval_status'   => 'Draft',
            'created_by'        => $staff->id,
            'created_at'        => Carbon::now()->subHours(2),
            'updated_at'        => Carbon::now()->subHours(2),
        ]);
        $this->addMaterials($f5, [
            [$mats['Ekstrak Jahe']->id,         $sup1->id, 30],
            [$mats['Gula Aren']->id,            $sup2->id, 20],
            [$mats['Asam Jawa']->id,            $sup2->id, 15],
            [$mats['Kayu Manis Bubuk']->id,     $sup4->id, 10],
            [$mats['Air Mineral']->id,          $sup5->id, 25],
        ]);

        // ──────────────────────────────────────────────────
        // FORMULA 6: Jahe Merah V2 — reformulasi (DRAFT)
        // kode berbeda karena unique constraint
        // ──────────────────────────────────────────────────
        $f6 = Formula::create([
            'code'              => 'FRM-202607-004',
            'name'              => 'Jahe Merah Hangat',
            'version'           => 2,
            'parent_formula_id' => $f1->id,
            'development_stage' => 'Optimalisasi',
            'approval_status'   => 'Draft',
            'created_by'        => $staff->id,
            'created_at'        => Carbon::now()->subDay(),
            'updated_at'        => Carbon::now()->subDay(),
        ]);
        $this->addMaterials($f6, [
            [$mats['Ekstrak Jahe']->id,         $sup1->id, 40],
            [$mats['Madu Murni']->id,           $sup3->id, 20],
            [$mats['Kayu Manis Bubuk']->id,     $sup4->id, 10],
            [$mats['Gula Aren']->id,            $sup2->id, 20],
            [$mats['Air Mineral']->id,          $sup5->id, 10],
        ]);

        // ──────────────────────────────────────────────────
        // TRIAL RM 1 — Formula Jahe V1 (Lulus)
        // ──────────────────────────────────────────────────
        $t1 = TrialRm::create([
            'code'            => 'TRM-202606-001-A',
            'formula_id'      => $f1->id,
            'sample_identity' => 'Batch JM-001-A — 500ml prototype',
            'process_steps'   => "1. Campur ekstrak jahe dengan air mineral (suhu 60°C)\n2. Tambahkan madu murni, aduk rata\n3. Masukkan gula aren, larutkan sempurna\n4. Tambah kayu manis, homogenisasi 10 menit\n5. Dinginkan, saring, kemas",
            'decision'        => 'Lulus',
            'approval_status' => 'Approved',
            'created_by'      => $staff->id,
            'created_at'      => Carbon::now()->subDays(18),
            'updated_at'      => Carbon::now()->subDays(16),
        ]);
        $this->addVerifications($t1, [
            ['Warna',       'Kuning kecoklatan', 'Kuning kecoklatan', 'Pass'],
            ['Aroma',       'Pedas jahe khas',   'Pedas jahe khas',   'Pass'],
            ['Rasa',        'Manis-pedas',        'Manis-pedas',       'Pass'],
            ['pH',          '5.5 – 6.0',         '5.7',               'Pass'],
            ['Viskositas',  '1200 – 1500 cP',    '1350 cP',           'Pass'],
            ['Berat Jenis', '1.05 – 1.10',        '1.07',              'Pass'],
        ]);

        // ──────────────────────────────────────────────────
        // TRIAL RM 2 — Formula Temulawak (In Progress)
        // ──────────────────────────────────────────────────
        $t2 = TrialRm::create([
            'code'            => 'TRM-202607-001-A',
            'formula_id'      => $f3->id,
            'sample_identity' => 'Batch TW-001-A — 250ml uji coba',
            'process_steps'   => "1. Larutkan temulawak bubuk dalam air panas (70°C)\n2. Tambahkan ekstrak jahe, aduk 5 menit\n3. Masukkan madu dan gula aren\n4. Homogenisasi 15 menit pada 40°C",
            'decision'        => null,
            'approval_status' => 'Draft',
            'created_by'      => $staff->id,
            'created_at'      => Carbon::now()->subDays(2),
            'updated_at'      => Carbon::now()->subHours(6),
        ]);
        $this->addVerifications($t2, [
            ['Warna',       'Kuning tua',        'Kuning tua',     'Pass'],
            ['Aroma',       'Harum temulawak',   'Harum temulawak','Pass'],
            ['Rasa',        'Pahit-manis',       '',               'Pass'],
            ['pH',          '5.0 – 5.5',         '',               'Pass'],
            ['Viskositas',  '1000 – 1300 cP',    '',               'Pass'],
            ['Berat Jenis', '1.03 – 1.08',        '',               'Pass'],
        ]);

        // ──────────────────────────────────────────────────
        // TRIAL PM 1 — Botol PET 250ml (4/4 Approved)
        // ──────────────────────────────────────────────────
        $tp1 = TrialPm::create([
            'code'               => 'TPM-202606-001',
            'packaging_material' => 'Botol PET 250ml dengan tutup flip-top',
            'specifications'     => 'Botol PET grade food, kapasitas 250ml, diameter 55mm, tinggi 140mm. Tutup flip-top HDPE warna hijau.',
            'parameters'         => ['kecepatan_filling' => '80 botol/menit', 'suhu_sealing' => '180°C', 'tekanan_mesin' => '4.5 bar'],
            'risk_analysis'      => 'Risiko utama kebocoran pada sambungan tutup. Mitigasi: uji torque 15 Nm minimum.',
            'approval_status'    => 'Approved',
            'created_by'         => $staff->id,
            'created_at'         => Carbon::now()->subDays(12),
            'updated_at'         => Carbon::now()->subDays(7),
        ]);
        foreach (['rd' => 'R&D', 'qc' => 'QC', 'production' => 'Produksi', 'engineering' => 'Engineering'] as $dept => $label) {
            TrialPmApproval::create([
                'trial_pm_id' => $tp1->id,
                'department'  => $dept,
                'approved_by' => $manager->id,
                'notes'       => "$label telah memeriksa dan menyetujui.",
                'is_approved' => true,
                'approved_at' => Carbon::now()->subDays(7),
            ]);
        }

        // ──────────────────────────────────────────────────
        // TRIAL PM 2 — Sachet Alu-foil (2/4 Approved)
        // ──────────────────────────────────────────────────
        $tp2 = TrialPm::create([
            'code'               => 'TPM-202607-001',
            'packaging_material' => 'Sachet aluminium foil 30ml',
            'specifications'     => 'Sachet alu-foil 4-layer (PET/AL/NY/PE), 80x120mm, kapasitas 30ml. Sealing 8mm.',
            'parameters'         => ['kecepatan_filling' => '120 sachet/menit', 'suhu_sealing' => '210°C', 'tekanan_mesin' => '5.0 bar'],
            'risk_analysis'      => 'Risiko kebocoran pada sealing. Uji kebocoran water immersion test 30 menit.',
            'approval_status'    => 'Draft',
            'created_by'         => $siti->id,
            'created_at'         => Carbon::now()->subDays(4),
            'updated_at'         => Carbon::now()->subHours(3),
        ]);
        foreach (['rd' => 'R&D', 'qc' => 'QC'] as $dept => $label) {
            TrialPmApproval::create([
                'trial_pm_id' => $tp2->id,
                'department'  => $dept,
                'approved_by' => $staff->id,
                'notes'       => "$label menyetujui dari sisi teknis.",
                'is_approved' => true,
                'approved_at' => Carbon::now()->subDays(2),
            ]);
        }

        $this->command->info('✅ Demo data seeded:');
        $this->command->info('   📋 ' . Formula::count() . ' Formulas (1 Draft, 1 Pending T1, 1 Pending T2, 2 Approved, 1 V2)');
        $this->command->info('   🧪 ' . TrialRm::count() . ' Trial RMs (1 Lulus, 1 In Progress)');
        $this->command->info('   📦 ' . TrialPm::count() . ' Trial PMs (1 Approved 4/4, 1 Pending 2/4)');
    }

    private function addMaterials(Formula $formula, array $items): void
    {
        foreach ($items as [$materialId, $supplierId, $pct]) {
            FormulaMaterial::create([
                'formula_id'  => $formula->id,
                'material_id' => $materialId,
                'supplier_id' => $supplierId,
                'percentage'  => $pct,
            ]);
        }
    }

    private function addVerifications(TrialRm $trial, array $items): void
    {
        foreach ($items as [$param, $target, $actual, $status]) {
            TrialRmVerification::create([
                'trial_rm_id'    => $trial->id,
                'parameter_name' => $param,
                'target_value'   => $target,
                'actual_value'   => $actual,
                'status'         => $status,
            ]);
        }
    }
}
