<?php

namespace Tests\Feature;

use App\Models\NpdProposal;
use App\Models\Prf;
use App\Models\SampleEvaluation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SampleEvaluationSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function makeNpdProposal(User $staff): NpdProposal
    {
        $prf = Prf::create([
            'code'            => 'PRF-001',
            'product_name'    => 'Kopi Instan',
            'product_concept' => 'Konsep kopi instan',
            'target_market'   => 'Indonesia',
            'product_category' => 'Minuman',
            'target_launch'   => '2027-01-01',
            'created_by'      => $staff->id,
        ]);

        return NpdProposal::create([
            'code'                 => 'NPD-202608-001',
            'prf_id'               => $prf->id,
            'product_name'         => 'Kopi Instan',
            'product_concept'      => 'Konsep kopi instan',
            'target_cogs'          => 5000,
            'target_selling_price' => 15000,
            'development_start'    => '2026-08-01',
            'development_end'      => '2027-01-01',
            'pic'                  => $staff->name,
            'project_team'         => 'R&D',
            'project_status'       => 'In Progress',
            'created_by'           => $staff->id,
        ]);
    }

    private function makeEvaluation(User $staff, ?NpdProposal $proposal = null): SampleEvaluation
    {
        return SampleEvaluation::create([
            'sample_id'        => 'SEV-202608-001',
            'product_name'     => $proposal?->product_name ?? 'Kopi Instan',
            'npd_proposal_id'  => $proposal?->id,
            'project_owner_id' => $staff->id,
            'status'           => 'In Progress',
            'created_by'       => $staff->id,
        ]);
    }

    public function test_full_sample_evaluation_flow(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $staff = User::factory()->create(['email' => 'sev-staff@test.com']);
        $other = User::factory()->create(['email' => 'sev-other@test.com']);
        $owner = User::factory()->create(['email' => 'sev-owner@test.com']);
        $om = User::factory()->create(['email' => 'sev-om@test.com']);
        $gm = User::factory()->create(['email' => 'sev-gm@test.com']);
        $staff->assignRole('Staff R&D');
        $other->assignRole('Staff R&D');
        $om->assignRole('Operational Manager');
        $gm->assignRole('General Manager');

        $this->assertTrue($staff->can('sample_evaluation.view'));
        $this->assertTrue($staff->can('sample_evaluation.create'));
        $this->assertTrue($om->can('sample_evaluation.view'));
        $this->assertFalse($om->can('sample_evaluation.create'));

        // ── Index + create ──
        $this->actingAs($staff)->get('/sample-evaluations')->assertOk()->assertSee('Sample Evaluation');
        $this->actingAs($staff)->get('/sample-evaluations/create')
            ->assertOk()
            ->assertSee('Project Owner')
            ->assertSee('NPD Proposal');

        $proposal = $this->makeNpdProposal($staff);

        // ── Store (Product Name diambil dari NPD Proposal) ──
        $this->actingAs($staff)->post('/sample-evaluations', [
            'sample_id'        => 'SEV-202608-001',
            'npd_proposal_id'  => $proposal->id,
            'project_owner_id' => $owner->id,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sample_evaluations', [
            'sample_id'        => 'SEV-202608-001',
            'product_name'     => 'Kopi Instan',
            'npd_proposal_id'  => $proposal->id,
            'project_owner_id' => $owner->id,
            'status'           => 'In Progress',
        ]);

        $evaluation = SampleEvaluation::first();

        // ── Show (link ke NPD Proposal) ──
        $this->actingAs($staff)->get("/sample-evaluations/{$evaluation->id}")
            ->assertOk()
            ->assertSee('SEV-202608-001')
            ->assertSee(route('npd-proposals.show', $proposal))
            ->assertSee('Tambah Sesi Evaluasi');

        // ── Store session + 5 parameter ──
        $this->actingAs($staff)->post("/sample-evaluations/{$evaluation->id}/sessions", [
            'trial_batch'    => 1,
            'evaluator_type' => 'Internal',
            'decision'       => 'Approved',
            'parameters'     => [
                'Rasa'       => ['score' => 'Manis, pedas seimbang', 'note' => 'Enak'],
                'Warna'      => ['score' => 'Kuning kecoklatan', 'note' => 'Sedikit pucat'],
                'Aroma'      => ['score' => 'Aroma jahe kuat', 'note' => null],
                'Tekstur'    => ['score' => 'Kental', 'note' => null],
                'After Taste' => ['score' => 'Hangat', 'note' => null],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sample_evaluation_sessions', [
            'sample_evaluation_id' => $evaluation->id,
            'session_no'           => 1,
            'trial_batch'          => 1,
            'decision'             => 'Approved',
        ]);
        $this->assertDatabaseHas('sample_evaluation_parameters', [
            'parameter' => 'After Taste',
            'score'     => 'Hangat',
        ]);

        // Status header otomatis mengikuti decision sesi
        $evaluation->refresh();
        $this->assertEquals('Approved', $evaluation->status);

        // ── Attachment upload ──
        Storage::fake('public');
        $session = $evaluation->sessions()->first();
        $this->actingAs($staff)->post("/sample-evaluations/{$evaluation->id}/sessions/{$session->id}/attachments", [
            'type' => 'Form Panel',
            'file' => UploadedFile::fake()->create('form-panel.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sample_evaluation_attachments', [
            'session_id' => $session->id,
            'type'       => 'Form Panel',
        ]);

        // ── OM/GM hanya bisa lihat, tidak bisa edit/create ──
        $this->actingAs($om)->get("/sample-evaluations/{$evaluation->id}")->assertOk();
        $this->actingAs($om)->get('/sample-evaluations/create')->assertForbidden();
        $this->actingAs($om)->post("/sample-evaluations/{$evaluation->id}/sessions", [
            'trial_batch'    => 2,
            'evaluator_type' => 'Internal',
            'parameters'     => [
                'Rasa' => ['score' => 'Enak'], 'Warna' => ['score' => 'Cerah'],
                'Aroma' => ['score' => 'Kuat'], 'Tekstur' => ['score' => 'Kental'],
                'After Taste' => ['score' => 'Hangat'],
            ],
        ])->assertForbidden();

        // ── Staff lain tidak bisa lihat/edit punya staff lain ──
        $this->actingAs($other)->get("/sample-evaluations/{$evaluation->id}")->assertForbidden();
        $this->actingAs($other)->get("/sample-evaluations/{$evaluation->id}/edit")->assertForbidden();

        // ── Edit + hapus ──
        $this->actingAs($staff)->get("/sample-evaluations/{$evaluation->id}/edit")->assertOk();
        $this->actingAs($staff)->put("/sample-evaluations/{$evaluation->id}", [
            'sample_id'        => 'SEV-202608-001',
            'npd_proposal_id'  => $proposal->id,
            'project_owner_id' => $owner->id,
        ])->assertSessionHasNoErrors();

        $this->actingAs($staff)->delete("/sample-evaluations/{$evaluation->id}")
            ->assertRedirect(route('sample-evaluations.index'));
        $this->assertDatabaseMissing('sample_evaluations', ['id' => $evaluation->id]);
        $this->assertDatabaseMissing('sample_evaluation_sessions', ['sample_evaluation_id' => $evaluation->id]);
    }
}