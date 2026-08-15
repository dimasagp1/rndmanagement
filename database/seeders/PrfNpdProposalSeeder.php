<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Prf;
use App\Models\NpdProposal;
use Carbon\Carbon;

class PrfNpdProposalSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        NpdProposal::whereIn('code', [
            'NPD-202606-001', 'NPD-202606-002',
            'NPD-202607-001', 'NPD-202607-002', 'NPD-202607-003',
            'NPD-202608-001',
        ])->delete();
        Prf::whereIn('code', [
            'PRF-202606-001', 'PRF-202606-002',
            'PRF-202607-001', 'PRF-202607-002', 'PRF-202607-003',
            'PRF-202607-004', 'PRF-202607-005', 'PRF-202608-001',
        ])->delete();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $staff   = User::where('email', 'staff@herbatech.com')->first();
        $siti    = User::where('email', 'siti@herbatech.com')->first();

        if (! $staff || ! $siti) {
            $this->command->error('❌ Jalankan UserSeeder terlebih dahulu! (php artisan db:seed --class=UserSeeder)');
            return;
        }

        $prf1 = $this->createPrf([
            'code' => 'PRF-202606-001',
            'product_concept' => "Minuman herbal siap minum berbasis jahe merah dengan madu murni sebagai pemanis alami.\n\nDitujukan untuk konsumen yang mencari produk penghangat tubuh dengan cita rasa pedas-manis khas, tanpa bahan pengawet.\n\nKemasan botol PET 250ml dengan tutup flip-top.",
            'target_market' => 'Generik & Premium', 'product_category' => 'COD',
            'target_launch' => '2026-09-01', 'product_name' => 'Jahe Merah Hangat',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subDays(30),
        ]);

        $prf2 = $this->createPrf([
            'code' => 'PRF-202606-002',
            'product_concept' => "Sari kurma premium yang dikombinasikan dengan madu sebagai energi booster alami.\n\nTarget konsumen pekerja aktif dan ibu hamil yang membutuhkan asupan energi praktis.\n\nKemasan sachet 15ml, 10 sachet per box.",
            'target_market' => 'Premium', 'product_category' => 'COD',
            'target_launch' => '2026-10-15', 'product_name' => 'Sari Kurma Madu',
            'created_by' => $siti->id,
            'created_at' => Carbon::now()->subDays(20),
        ]);

        $prf3 = $this->createPrf([
            'code' => 'PRF-202607-001',
            'product_concept' => "Minuman kunyit asam segar dengan rasa asam-manis yang menyegarkan.\n\nMenggunakan kunyit pilihan dan asam jawa, cocok untuk konsumen wanita yang menginginkan minuman kesehatan harian.\n\nKemasan botol PET 250ml.",
            'target_market' => 'Mass Market', 'product_category' => 'COD',
            'target_launch' => '2026-11-01', 'product_name' => 'Kunyit Asam Segar',
            'created_by' => $siti->id,
            'created_at' => Carbon::now()->subDays(14),
        ]);

        $prf4 = $this->createPrf([
            'code' => 'PRF-202607-002',
            'product_concept' => "Teh celup herbal dari daun jati cina untuk membantu program diet alami.\n\nTanpa gula tambahan, dikemas dalam kotak 25 kantong teh.\n\nFile pendukung (analisa pasar & referensi BPOM) sudah dilampirkan.",
            'target_market' => 'Generik', 'product_category' => 'Serbuk',
            'target_launch' => '2026-12-01', 'product_name' => 'Teh Herbal Daun Jati Cina',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subDays(3),
        ]);

        $prf5 = $this->createPrf([
            'code' => 'PRF-202607-003',
            'product_concept' => "Jamu tradisional beras kencur dalam kemasan botol 200ml.\n\nKonsep masih perlu disempurnakan: target market belum jelas dan komposisi gula masih terlalu tinggi.",
            'target_market' => null, 'product_category' => 'COD',
            'target_launch' => null, 'product_name' => 'Jamu Beras Kencur Tradisional',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subDays(8),
        ]);

        $prf6 = $this->createPrf([
            'code' => 'PRF-202607-004',
            'product_concept' => "Susu jahe bubuk instan kemasan sachet untuk segmen keluarga.\n\nPerpaduan susu bubuk, gula aren, dan jahe merah bubuk. Cukup diseduh air panas.\n\nDiajukan untuk segera dibuatkan NPD Proposal.",
            'target_market' => 'Mass Market', 'product_category' => 'Serbuk',
            'target_launch' => '2027-01-15', 'product_name' => 'Susu Jahe Bubuk Instan',
            'created_by' => $siti->id,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        $prf7 = $this->createPrf([
            'code' => 'PRF-202607-005',
            'product_concept' => "Kapsul ekstrak temulawak untuk meningkatkan daya tahan tubuh.\n\nDosis 500mg per kapsul, 60 kapsul per botol.\n\nBasis untuk pengembangan NPD Proposal lini produk suplemen herbal.",
            'target_market' => 'Premium', 'product_category' => 'Kapsul',
            'target_launch' => '2026-12-20', 'product_name' => 'Ekstrak Temulawak Kapsul',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        $prf8 = $this->createPrf([
            'code' => 'PRF-202608-001',
            'product_concept' => "Sediaan herbal sirup batuk anak dengan rasa jeruk yang ramah anak.\n\nBerbasis ekstrak jahe, madu, dan jeruk nipis. Tanpa alkohol.\n\nMasih dalam penyusunan konsep awal, belum diajukan.",
            'target_market' => 'Generik', 'product_category' => 'COD',
            'target_launch' => '2027-03-01', 'product_name' => 'Sirup Herbal Batuk Anak',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subHours(6),
        ]);

        $this->createNpdProposal([
            'code' => 'NPD-202606-001', 'prf' => $prf1,
            'product_name' => 'Jahe Merah Hangat',
            'product_concept' => "Minuman herbal siap minum berbasis jahe merah dengan madu murni.\n\nTarget: 12.500 botol/bulan di tahun pertama dengan coverage 5 kota besar.",
            'target_cogs' => 12500, 'target_selling_price' => 25000,
            'development_start' => '2026-07-01', 'development_end' => '2026-12-15',
            'pic' => 'Ahmad Fauzi',
            'project_team' => "Ahmad Fauzi (PIC / Formulasi)\nSiti Nurhaliza (Analisis Pasar)\nBudi Santoso (Produksi)\nCitra Lestari (QA/QC)\nDeni Maulana (Packaging)",
            'project_status' => 'On Track',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subDays(21),
        ]);

        $this->createNpdProposal([
            'code' => 'NPD-202606-002', 'prf' => $prf2,
            'product_name' => 'Sari Kurma Madu',
            'product_concept' => "Sari kurma premium dengan madu, kemasan sachet 15ml.\n\nPengembangan formulasi sedang berjalan.",
            'target_cogs' => 8200, 'target_selling_price' => 18000,
            'development_start' => '2026-08-01', 'development_end' => '2027-02-28',
            'pic' => 'Siti Nurhaliza',
            'project_team' => "Siti Nurhaliza (PIC)\nRina Ayu (Formulasi)\nBudi Santoso (Produksi)",
            'project_status' => 'In Progress',
            'created_by' => $siti->id,
            'created_at' => Carbon::now()->subDays(10),
        ]);

        $this->createNpdProposal([
            'code' => 'NPD-202607-001', 'prf' => $prf3,
            'product_name' => 'Kunyit Asam Segar',
            'product_concept' => "Minuman kunyit asam segar untuk segmen mass market.\n\nProyek berjalan sesuai jadwal, formulasi prototype batch 1 telah selesai.",
            'target_cogs' => 9800, 'target_selling_price' => 19000,
            'development_start' => '2026-07-15', 'development_end' => '2027-01-31',
            'pic' => 'Siti Nurhaliza',
            'project_team' => "Siti Nurhaliza (PIC)\nAhmad Fauzi (Formulasi)\nCitra Lestari (QA/QC)\nDeni Maulana (Packaging)",
            'project_status' => 'In Progress',
            'created_by' => $siti->id,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        $this->createNpdProposal([
            'code' => 'NPD-202607-002', 'prf' => $prf7,
            'product_name' => 'Ekstrak Temulawak Kapsul',
            'product_concept' => "Kapsul ekstrak temulawak 500mg untuk daya tahan tubuh.\n\nMasih dalam tahap perencanaan, pengembangan formulasi belum dimulai.",
            'target_cogs' => 14500, 'target_selling_price' => 32000,
            'development_start' => '2026-09-01', 'development_end' => '2027-03-31',
            'pic' => 'Ahmad Fauzi',
            'project_team' => "Ahmad Fauzi (PIC)\nRina Ayu (Formulasi)\nCitra Lestari (QA/QC)",
            'project_status' => 'Draft',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subDay(),
        ]);

        $this->createNpdProposal([
            'code' => 'NPD-202607-003', 'prf' => $prf3,
            'product_name' => 'Kunyit Asam Segar — Varian Botol 500ml',
            'product_concept' => "Varian kemasan botol PET 500ml untuk konsumsi keluarga.\n\nTerkendala: margin target terlalu tipis terhadap harga jual, sedang evaluasi ulang struktur biaya.",
            'target_cogs' => 17000, 'target_selling_price' => 29000,
            'development_start' => '2026-08-01', 'development_end' => '2027-04-30',
            'pic' => 'Siti Nurhaliza',
            'project_team' => "Siti Nurhaliza (PIC)\nAhmad Fauzi (Formulasi)",
            'project_status' => 'On Hold',
            'created_by' => $siti->id,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        $this->createNpdProposal([
            'code' => 'NPD-202608-001', 'prf' => $prf4,
            'product_name' => 'Teh Herbal Daun Jati Cina',
            'product_concept' => "Teh celup herbal daun jati cina untuk program diet alami.\n\nProposal masih berupa draft, detail proyek sedang disusun.",
            'target_cogs' => 6500, 'target_selling_price' => 15000,
            'development_start' => '2026-09-15', 'development_end' => '2027-05-31',
            'pic' => 'Ahmad Fauzi',
            'project_team' => "Ahmad Fauzi (PIC)\nRina Ayu (Formulasi)\nDeni Maulana (Packaging)",
            'project_status' => 'Draft',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subHours(3),
        ]);

        $this->command->info('✅ PRF & NPD Proposal demo data seeded:');
        $this->command->info('   📝 ' . Prf::count() . ' PRF');
        $this->command->info('   🚀 ' . NpdProposal::count() . ' NPD Proposal');
    }

    private function createPrf(array $data): Prf
    {
        return Prf::create([
            'code'              => $data['code'],
            'product_concept'   => $data['product_concept'],
            'target_market'     => $data['target_market'],
            'product_category'  => $data['product_category'],
            'target_launch'     => $data['target_launch'],
            'product_name'      => $data['product_name'],
            'created_by'        => $data['created_by'],
            'created_at'        => $data['created_at'] ?? now(),
            'updated_at'        => $data['created_at'] ?? now(),
        ]);
    }

    private function createNpdProposal(array $data): NpdProposal
    {
        return NpdProposal::create([
            'code'               => $data['code'],
            'prf_id'             => $data['prf']->id,
            'product_name'       => $data['product_name'],
            'product_concept'    => $data['product_concept'],
            'target_cogs'        => $data['target_cogs'],
            'target_selling_price' => $data['target_selling_price'],
            'development_start'  => $data['development_start'],
            'development_end'    => $data['development_end'],
            'pic'                => $data['pic'],
            'project_team'       => $data['project_team'],
            'project_status'     => $data['project_status'],
            'created_by'         => $data['created_by'],
            'created_at'         => $data['created_at'] ?? now(),
            'updated_at'         => $data['created_at'] ?? now(),
        ]);
    }
}