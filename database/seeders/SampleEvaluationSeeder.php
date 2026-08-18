<?php

namespace Database\Seeders;

use App\Models\NpdProposal;
use App\Models\SampleEvaluation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SampleEvaluationSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        SampleEvaluation::whereIn('sample_id', [
            'SEV-202608-001', 'SEV-202608-002', 'SEV-202608-003',
        ])->delete();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $staff   = User::where('email', 'staff@herbatech.com')->first();
        $siti    = User::where('email', 'siti@herbatech.com')->first();

        $jahe   = NpdProposal::where('code', 'NPD-202606-001')->first();
        $kurma  = NpdProposal::where('code', 'NPD-202606-002')->first();
        $kunyit = NpdProposal::where('code', 'NPD-202607-001')->first();

        // ──────────────────────────────────────────────────
        // SAMPLE 1: Jahe Merah Hangat — APPROVED (2 sesi)
        // ──────────────────────────────────────────────────
        $s1 = SampleEvaluation::create([
            'sample_id'        => 'SEV-202608-001',
            'product_name'     => $jahe->product_name,
            'npd_proposal_id'  => $jahe->id,
            'project_owner_id' => $staff->id,
            'status'           => 'Approved',
            'created_by'       => $staff->id,
            'created_at'       => Carbon::now()->subDays(15),
            'updated_at'       => Carbon::now()->subDays(7),
        ]);
        $ses1 = $s1->sessions()->create([
            'session_no'        => 1,
            'trial_batch'       => 1,
            'evaluator_type'    => 'Internal',
            'evaluation_result' => "Panel internal 10 orang menyatakan rasa pedas jahe diterima, kekentalan sedikit kurang.",
            'sensory_result'    => "Dominan pedas jahe dengan sentuhan manis madu, after taste hangat.",
            'decision'          => 'Approved',
            'evaluated_by'      => $staff->id,
            'evaluated_at'      => Carbon::now()->subDays(14),
            'created_at'        => Carbon::now()->subDays(14),
            'updated_at'        => Carbon::now()->subDays(14),
        ]);
        $this->addParameters($ses1, [
            ['Rasa', 'Baik',   'Pedas jahe pas, manis seimbang'],
            ['Warna', 'Baik',  'Kuning kecoklatan khas'],
            ['Aroma', 'Baik',  'Aroma jahe kuat'],
            ['Tekstur', 'Cukup', 'Kekentalan sedikit kurang'],
            ['After Taste', 'Cukup', 'After taste agak lama'],
        ]);
        $ses2 = $s1->sessions()->create([
            'session_no'        => 2,
            'trial_batch'       => 2,
            'evaluator_type'    => 'External',
            'evaluation_result' => "Panel eksternal 20 orang, 90% menyukai produk, saran penambahan gula dikurangi.",
            'sensory_result'    => "Keseimbangan rasa manis-pedas diterima oleh mayoritas panelis.",
            'decision'          => 'Approved',
            'evaluated_by'      => $siti->id,
            'evaluated_at'      => Carbon::now()->subDays(7),
            'created_at'        => Carbon::now()->subDays(7),
            'updated_at'        => Carbon::now()->subDays(7),
        ]);
        $this->addParameters($ses2, [
            ['Rasa', 'Baik',   'Manis-pedas seimbang'],
            ['Warna', 'Baik',  null],
            ['Aroma', 'Baik',  null],
            ['Tekstur', 'Baik', 'Kekentalan sudah sesuai'],
            ['After Taste', 'Baik', null],
        ]);

        // ──────────────────────────────────────────────────
        // SAMPLE 2: Sari Kurma Madu — IN PROGRESS (1 sesi)
        // ──────────────────────────────────────────────────
        $s2 = SampleEvaluation::create([
            'sample_id'        => 'SEV-202608-002',
            'product_name'     => $kurma->product_name,
            'npd_proposal_id'  => $kurma->id,
            'project_owner_id' => $siti->id,
            'status'           => 'In Progress',
            'created_by'       => $siti->id,
            'created_at'       => Carbon::now()->subDays(3),
            'updated_at'       => Carbon::now()->subHours(20),
        ]);
        $ses3 = $s2->sessions()->create([
            'session_no'        => 1,
            'trial_batch'       => 1,
            'evaluator_type'    => 'Internal',
            'evaluation_result' => "Kurma dirasa kurang pekat, perlu penyesuaian takaran.",
            'sensory_result'    => "Aroma kurma khas, rasa manis dominan.",
            'decision'          => null,
            'evaluated_by'      => $staff->id,
            'evaluated_at'      => Carbon::now()->subHours(20),
            'created_at'        => Carbon::now()->subHours(20),
            'updated_at'        => Carbon::now()->subHours(20),
        ]);
        $this->addParameters($ses3, [
            ['Rasa', 'Cukup',  'Kurma kurang pekat'],
            ['Warna', 'Baik',  'Coklat muda sesuai target'],
            ['Aroma', 'Baik',  null],
            ['Tekstur', 'Cukup', 'Terlalu cair'],
            ['After Taste', 'Baik', null],
        ]);

        // ──────────────────────────────────────────────────
        // SAMPLE 3: Kunyit Asam Segar — REFORM (1 sesi)
        // ──────────────────────────────────────────────────
        $s3 = SampleEvaluation::create([
            'sample_id'        => 'SEV-202608-003',
            'product_name'     => $kunyit->product_name,
            'npd_proposal_id'  => $kunyit->id,
            'project_owner_id' => $siti->id,
            'status'           => 'Reform',
            'created_by'       => $staff->id,
            'created_at'       => Carbon::now()->subDays(2),
            'updated_at'       => Carbon::now()->subDay(),
        ]);
        $ses4 = $s3->sessions()->create([
            'session_no'        => 1,
            'trial_batch'       => 1,
            'evaluator_type'    => 'External',
            'evaluation_result' => "Panelis menilai rasa terlalu asam, warna kurang menarik.",
            'sensory_result'    => "Asam mendominasi, after taste pahit.",
            'decision'          => 'Reform',
            'evaluated_by'      => $staff->id,
            'evaluated_at'      => Carbon::now()->subDay(),
            'created_at'        => Carbon::now()->subDay(),
            'updated_at'        => Carbon::now()->subDay(),
        ]);
        $this->addParameters($ses4, [
            ['Rasa', 'Kurang', 'Terlalu asam'],
            ['Warna', 'Kurang', 'Warna kurang menarik'],
            ['Aroma', 'Cukup',  'Aroma kunyit kurang kuat'],
            ['Tekstur', 'Baik', null],
            ['After Taste', 'Kurang', 'After taste pahit'],
        ]);

        $this->command->info('🌿 Sample Evaluation demo data seeded!');
    }

    private function addParameters($session, array $rows): void
    {
        foreach ($rows as [$parameter, $score, $note]) {
            $session->parameters()->create([
                'parameter' => $parameter,
                'score'     => $score,
                'note'      => $note,
            ]);
        }
    }
}