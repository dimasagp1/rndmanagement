<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Formula;
use Carbon\Carbon;

class TimelineSeeder extends Seeder
{
    private const STAGES = [
        'Draf', 'Pra-Trial', 'Optimalisasi', 'Final', 'Product Form',
        'Laboratory Trial', 'Sensory Test', 'Plant Trial', 'Market Test',
    ];

    private const PRODUCTS = [
        'Sirup Rosella Merah', 'Minuman Sari Kurma Energi', 'Teh Herbal Daun Salam',
        'Madu Propolis Imun', 'Jamu Kunyit Putih', 'Minuman Probiotik Tempe',
        'Ekstrak Pegagan Fokus', 'Sirup Belimbing Wuluh', 'Jahe Serai Hangat',
        'Minuman Cokelat Ginseng', 'Teh Bunga Telang', 'Madu Jahe Lemon',
        'Jamu Cabe Puyang', 'Minuman Beras Hitam', 'Sirup Lidah Buaya Aloe',
        'Kunyit Hitam Legacy', 'Teh Daun Kelor', 'Minuman Alpukat Kacang',
        'Jamu Gula Asem', 'Minuman Cengkeh Kayu Manis', 'Sirup Markisa Herbal',
        'Teh Hijau Matcha Rempah', 'Jamu Brotowali Kuat', 'Minuman Kurma Susu Kambing',
        'Sirup Nanas Jahe',
    ];

    public function run(): void
    {
        // Idempotent: hapus data timeline yang pernah di-seed sebelumnya
        Formula::where('code', 'like', 'FRM-202608-%')
            ->orWhere('code', 'like', 'FRM-202609-%')
            ->delete();

        $staff   = User::where('email', 'staff@herbatech.com')->first();
        $siti    = User::where('email', 'siti@herbatech.com')->first();
        $owner   = User::where('email', 'owner@herbatech.com')->first();
        $manager = User::where('email', 'manager@herbatech.com')->first();
        $gm      = User::where('email', 'lisa@herbatech.com')->first();

        $users = array_filter([$staff, $siti, $owner, $manager, $gm]);
        if (empty($users)) {
            $this->command->error('Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        // Distribusi status agar semua area tampil terisi
        $plan = [];
        foreach (array_slice(self::PRODUCTS, 0, 5) as $name) {
            $plan[] = ['status' => 'Approved',          'days' => rand(-45, -5)];
        }
        foreach (array_slice(self::PRODUCTS, 5, 4) as $name) {
            $plan[] = ['status' => 'Completed',         'days' => rand(-60, -10)];
        }
        foreach (array_slice(self::PRODUCTS, 9, 6) as $name) {
            $plan[] = ['status' => 'Pending Tahap 1',   'days' => rand(1, 10)];
        }
        foreach (array_slice(self::PRODUCTS, 15, 5) as $name) {
            $plan[] = ['status' => 'Pending Tahap 2',   'days' => rand(4, 18)];
        }
        foreach (array_slice(self::PRODUCTS, 20, 4) as $name) {
            $plan[] = ['status' => 'Rejected',          'days' => rand(2, 14)];
        }
        foreach (array_slice(self::PRODUCTS, 24, 1) as $name) {
            $plan[] = ['status' => 'Draft',             'days' => rand(-3, 3)];
        }

        $now = Carbon::now();

        foreach ($plan as $i => $item) {
            $user = $users[$i % count($users)];
            $status = $item['status'];
            $target = $now->copy()->addDays($item['days']);

            $formula = Formula::create([
                'code'              => sprintf('FRM-202608-%03d', $i + 1),
                'name'              => self::PRODUCTS[$i],
                'version'           => 1,
                'development_stage' => self::STAGES[$i % count(self::STAGES)],
                'approval_status'   => $status,
                'created_by'        => $user->id,
                'approved_by_om'    => in_array($status, ['Approved', 'Completed', 'Pending Tahap 2']) ? $manager?->id : null,
                'approved_by_gm'    => in_array($status, ['Approved', 'Completed', 'Pending Tahap 2']) ? $gm?->id : null,
                'approved_at'       => in_array($status, ['Approved', 'Completed']) ? $now->copy()->addDays(rand(-45, -5)) : null,
                'rejection_notes'   => $status === 'Rejected' ? 'Perlu perbaikan komposisi dan evaluasi ulang stabilitas.' : null,
                'created_at'        => $target->copy()->subDays(rand(7, 21)),
                'updated_at'        => $target,
            ]);

            $this->command->line("   ✓ {$formula->code} | {$formula->name} | {$status} | {$formula->development_stage}");
        }

        $this->command->info('');
        $this->command->info('✅ TimelineSeeder selesai: ' . count($plan) . ' formula dibuat.');
        $this->command->info('   📊 Approved: ' . Formula::where('approval_status', 'Approved')->count());
        $this->command->info('   ✅ Completed: ' . Formula::where('approval_status', 'Completed')->count());
        $this->command->info('   ⏳ Pending Tahap 1: ' . Formula::where('approval_status', 'Pending Tahap 1')->count());
        $this->command->info('   ⏳ Pending Tahap 2: ' . Formula::where('approval_status', 'Pending Tahap 2')->count());
        $this->command->info('   🚫 Rejected: ' . Formula::where('approval_status', 'Rejected')->count());
        $this->command->info('   📝 Draft: ' . Formula::where('approval_status', 'Draft')->count());
    }
}
