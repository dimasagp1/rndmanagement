<?php

namespace Tests\Feature;

use App\Models\PreformulationStudy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QbdSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudy(User $creator): PreformulationStudy
    {
        return PreformulationStudy::create([
            'code'           => 'STD-QBD-001',
            'product_name'   => 'Kopi Instan QbD',
            'product_concept'=> 'Konsep kopi instan',
            'project_owner'  => $creator->name,
            'study_type'     => 'QBD Analysis',
            'status'         => 'In Progress',
            'approval_status'=> 'Approved',
            'created_by'     => $creator->id,
        ]);
    }

    public function test_full_qbd_wizard_flow(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $staff = User::factory()->create(['email' => 'qbd-staff@test.com']);
        $other = User::factory()->create(['email' => 'qbd-other@test.com']);
        $om = User::factory()->create(['email' => 'qbd-om@test.com']);
        $gm = User::factory()->create(['email' => 'qbd-gm@test.com']);
        $staff->assignRole('Staff R&D');
        $other->assignRole('Staff R&D');
        $om->assignRole('Operational Manager');
        $gm->assignRole('General Manager');

        $study = $this->makeStudy($staff);

        // ── Halaman wizard render untuk pemilik ──
        $this->assertTrue($staff->hasRole('Staff R&D'));
        $this->assertTrue($staff->can('npd_proposal.view'));
        $this->assertTrue($staff->can('view', $study));
        $this->actingAs($staff)->get('/preformulation-studies')->assertOk();
        $this->actingAs($staff)->get("/preformulation-studies/{$study->id}/qbd")
            ->assertOk()
            ->assertSee('QTPP')->assertSee('CQA')->assertSee('CMA')
            ->assertSee('CPP')->assertSee('Risk Assessment')->assertSee('Control Strategy');

        // ── QTPP ──
        $this->actingAs($staff)->post("/preformulation-studies/{$study->id}/qbd/qtpp", [
            'product_category' => 'Minuman Instan',
            'dosage_form'      => 'Serbuk',
            'target_market'    => 'Indonesia',
            'target_launch'    => '2027-01-01',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('preformulation_study_qtpps', [
            'study_id' => $study->id, 'product_category' => 'Minuman Instan',
        ]);

        $this->actingAs($staff)->post("/preformulation-studies/{$study->id}/qbd/qtpp-attributes", [
            'quality_attribute' => 'Kelarutan',
            'target'            => '≥ 95%',
            'unit'              => '%',
            'reference'         => 'USP',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('preformulation_study_qtpp_attributes', [
            'quality_attribute' => 'Kelarutan', 'target' => '≥ 95%',
        ]);

        // ── CQA ──
        $this->actingAs($staff)->post("/preformulation-studies/{$study->id}/qbd/cqa", [
            'quality_attribute' => 'Kelarutan',
            'target'            => '≥ 95% dalam 5 menit',
            'is_cqa'            => 'Y',
            'criticality'       => 'Critical',
            'justification'     => 'Memengaruhi bioavailabilitas',
            'reference'         => 'USP',
        ])->assertSessionHasNoErrors();

        $this->actingAs($staff)->post("/preformulation-studies/{$study->id}/qbd/cqa", [
            'quality_attribute' => 'Warna',
            'target'            => 'Coklat terang',
            'is_cqa'            => 'N',
            'criticality'       => 'Minor',
        ])->assertSessionHasNoErrors();

        $cqa = \App\Models\Qbd\Cqa::where('study_id', $study->id)->where('quality_attribute', 'Kelarutan')->first();
        $this->assertNotNull($cqa);

        // ── CMA ──
        $this->actingAs($staff)->post("/preformulation-studies/{$study->id}/qbd/cma", [
            'material'           => 'Biji Kopi Robusta',
            'material_attribute' => 'Particle Size',
            'target'             => '50',
            'unit'               => 'μm',
            'cqa_ids'            => [$cqa->id],
            'criticality'        => 'Critical',
            'justification'      => 'Menentukan laju kelarutan',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('preformulation_study_cmas', [
            'study_id' => $study->id, 'material' => 'Biji Kopi Robusta',
        ]);

        // ── CPP ──
        $this->actingAs($staff)->post("/preformulation-studies/{$study->id}/qbd/cpp", [
            'process_step'  => 'Spray Drying',
            'parameter'     => 'Suhu Inlet',
            'minimum'       => 160,
            'target'        => 170,
            'maximum'       => 180,
            'unit'          => '°C',
            'cqa_ids'       => [$cqa->id],
            'criticality'   => 'Major',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('preformulation_study_cpps', [
            'study_id' => $study->id, 'parameter' => 'Suhu Inlet',
        ]);

        // ── Risk Assessment: 2 × 3 × 4 = 24 → Medium ──
        $this->actingAs($staff)->post("/preformulation-studies/{$study->id}/qbd/risk", [
            'source_type'   => 'CMA',
            'source_name'   => 'Particle Size',
            'cqa_name'      => 'Kelarutan',
            'severity'      => 2,
            'occurrence'    => 3,
            'detectability' => 4,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('preformulation_study_risks', [
            'study_id' => $study->id, 'rpn' => 24, 'risk_level' => 'Medium',
        ]);

        $this->actingAs($staff)->post("/preformulation-studies/{$study->id}/qbd/risk", [
            'source_type'   => 'CPP',
            'source_name'   => 'Suhu Inlet',
            'cqa_name'      => 'Kelarutan',
            'severity'      => 5,
            'occurrence'    => 4,
            'detectability' => 3,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('preformulation_study_risks', [
            'study_id' => $study->id, 'rpn' => 60, 'risk_level' => 'High',
        ]);

        // ── Design Space: valid & invalid (min ≤ target ≤ max) ──
        $this->actingAs($staff)->post("/preformulation-studies/{$study->id}/qbd/design-space", [
            'parameter' => 'Suhu Inlet',
            'minimum'   => 160,
            'target'    => 170,
            'maximum'   => 180,
            'unit'      => '°C',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('preformulation_study_design_spaces', [
            'study_id' => $study->id, 'parameter' => 'Suhu Inlet',
        ]);

        $this->actingAs($staff)->post("/preformulation-studies/{$study->id}/qbd/design-space", [
            'parameter' => 'Suhu Outlet',
            'minimum'   => 100,
            'target'    => 90,
            'maximum'   => 80,
        ])->assertSessionHasErrors('design_space');
        $this->assertDatabaseMissing('preformulation_study_design_spaces', [
            'parameter' => 'Suhu Outlet',
        ]);

        // ── Control Strategy ──
        $this->actingAs($staff)->post("/preformulation-studies/{$study->id}/qbd/control-strategy", [
            'cqa'                    => 'Kelarutan',
            'control_point'          => 'Produk Jadi',
            'specification'          => '≥ 95% dalam 5 menit',
            'control_method'         => 'Uji Kelarutan',
            'monitoring'             => 'Per Batch',
            'frequency'              => 'Setiap batch',
            'responsible_department' => 'QC',
            'action_oos'             => 'Karantina & investigasi',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('preformulation_study_control_strategies', [
            'study_id' => $study->id, 'cqa' => 'Kelarutan',
        ]);

        // ── Hapus QTPP Attribute ──
        $attr = \App\Models\Qbd\QtppAttribute::where('quality_attribute', 'Kelarutan')->first();
        $this->actingAs($staff)->delete("/preformulation-studies/{$study->id}/qbd/qtpp-attributes/{$attr->id}")
            ->assertRedirect();
        $this->assertDatabaseMissing('preformulation_study_qtpp_attributes', ['id' => $attr->id]);

        // ── Summary step render ──
        $this->actingAs($staff)->get("/preformulation-studies/{$study->id}/qbd?step=8")
            ->assertOk()
            ->assertSee('Kelengkapan Modul')
            ->assertSee('QbD Summary');

        // ── Staff lain (bukan creator) tidak bisa akses ──
        $this->actingAs($other)->get("/preformulation-studies/{$study->id}/qbd")->assertForbidden();

        // ── Dashboard QbD ──
        $this->actingAs($staff)->get('/qbd')
            ->assertOk()
            ->assertSee('QbD Dashboard')
            ->assertSee('STD-QBD-001')
            ->assertSee('Progress Modul');

        // Staff lain hanya melihat study miliknya di dashboard
        $this->actingAs($other)->get('/qbd')
            ->assertOk()
            ->assertDontSee('STD-QBD-001');

        // ── Halaman detail menampilkan kartu QbD Progress ──
        $this->actingAs($staff)->get("/preformulation-studies/{$study->id}")
            ->assertOk()
            ->assertSee('QbD Progress')
            ->assertSee('Buka QbD Wizard');

        // ── OM & GM dapat membuka QbD (read-only untuk review approval) ──
        $this->actingAs($gm)->get('/qbd')
            ->assertOk()
            ->assertSee('QbD Dashboard')
            ->assertSee('STD-QBD-001');
        $this->actingAs($gm)->get("/preformulation-studies/{$study->id}")->assertOk();
        $this->actingAs($gm)->get("/preformulation-studies/{$study->id}/qbd")->assertOk();
        $this->actingAs($om)->get('/qbd')->assertOk();

        // Tapi tidak bisa mengubah data QbD
        $this->actingAs($gm)->post("/preformulation-studies/{$study->id}/qbd/cqa", [
            'quality_attribute' => 'Uji', 'target' => 'X', 'is_cqa' => 'Y', 'criticality' => 'Critical',
        ])->assertForbidden();

        // ── Reject dibatasi peran sesuai tahap (gap #1) ──
        $s2 = PreformulationStudy::create([
            'code' => 'STD-QBD-002', 'product_name' => 'Produk GM',
            'study_type' => 'QBD Analysis', 'status' => 'In Progress',
            'approval_status' => 'Draft', 'created_by' => $staff->id,
        ]);

        // OM tolak di Tahap 1 → boleh
        $s2->update(['approval_status' => 'Pending Tahap 1']);
        $this->actingAs($om)->post("/approval-center/preformulation-studies/{$s2->id}/reject", [
            'rejection_notes' => 'Data kurang lengkap',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('preformulation_studies', ['id' => $s2->id, 'approval_status' => 'Rejected']);

        // GM tolak di Tahap 1 → 403
        $s3 = PreformulationStudy::create([
            'code' => 'STD-QBD-003', 'product_name' => 'Produk Tahap 1',
            'study_type' => 'QBD Analysis', 'status' => 'In Progress',
            'approval_status' => 'Pending Tahap 1', 'created_by' => $staff->id,
        ]);
        $this->actingAs($gm)->post("/approval-center/preformulation-studies/{$s3->id}/reject", [
            'rejection_notes' => 'Coba tolak tahap 1',
        ])->assertForbidden();

        // OM tolak di Tahap 2 → 403
        $s3->update(['approval_status' => 'Pending Tahap 2', 'approved_by_om' => $om->id]);
        $this->actingAs($om)->post("/approval-center/preformulation-studies/{$s3->id}/reject", [
            'rejection_notes' => 'Coba tolak tahap 2',
        ])->assertForbidden();

        // GM tolak di Tahap 2 → boleh
        $this->actingAs($gm)->post("/approval-center/preformulation-studies/{$s3->id}/reject", [
            'rejection_notes' => 'Spesifikasi belum memenuhi standar',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('preformulation_studies', ['id' => $s3->id, 'approval_status' => 'Rejected']);
    }
}