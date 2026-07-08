<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Formula;
use App\Models\TrialRm;
use App\Models\TrialPm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalCenterTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $manager;
    private User $gm;
    private Formula $pendingFormulaT1;
    private Formula $pendingFormulaT2;
    private TrialRm $pendingTrialT1;
    private TrialRm $pendingTrialT2;
    private TrialPm $pendingTrialPm;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Roles and Permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // Create Users
        $this->staff = User::factory()->create();
        $this->staff->assignRole('Staff R&D');

        $this->manager = User::factory()->create();
        $this->manager->assignRole('Operational Manager');

        $this->gm = User::factory()->create();
        $this->gm->assignRole('General Manager');

        // Create Formulas
        $this->pendingFormulaT1 = Formula::create([
            'code'              => 'FRM-202607-001',
            'name'              => 'Formula T1',
            'version'           => 1,
            'development_stage' => 'Optimalisasi',
            'approval_status'   => 'Pending Tahap 1',
            'created_by'        => $this->staff->id,
        ]);

        $this->pendingFormulaT2 = Formula::create([
            'code'              => 'FRM-202607-002',
            'name'              => 'Formula T2',
            'version'           => 1,
            'development_stage' => 'Final',
            'approval_status'   => 'Pending Tahap 2',
            'created_by'        => $this->staff->id,
        ]);

        // Create Trial RMs
        $this->pendingTrialT1 = TrialRm::create([
            'code'            => 'TRM-202607-001-A',
            'formula_id'      => $this->pendingFormulaT2->id,
            'sample_identity' => 'Trial T1',
            'process_steps'   => 'Langkah 1',
            'approval_status' => 'Pending Tahap 1',
            'created_by'      => $this->staff->id,
        ]);

        $this->pendingTrialT2 = TrialRm::create([
            'code'            => 'TRM-202607-002-A',
            'formula_id'      => $this->pendingFormulaT2->id,
            'sample_identity' => 'Trial T2',
            'process_steps'   => 'Langkah 1',
            'approval_status' => 'Pending Tahap 2',
            'created_by'      => $this->staff->id,
        ]);

        // Create Trial PM
        $this->pendingTrialPm = TrialPm::create([
            'code'               => 'TPM-202607-001',
            'proposal_number'    => 'USUL-12345',
            'packaging_material' => 'Botol PET 250ml',
            'supplier'           => 'PT Kemas Makmur',
            'product_use'        => ['Jahe Merah'],
            'product_trial'      => ['Batch A'],
            'trial_sample_quantity' => '500 pcs',
            'specifications'     => ['Spesifikasi detail'],
            'risk_analysis'      => 'Resiko bocor',
            'approval_status'    => 'Pending Approval',
            'created_by'         => $this->staff->id,
        ]);
    }

    public function test_manager_sees_only_pending_tahap_1_documents_in_approval_center()
    {
        $response = $this->actingAs($this->manager)->get(route('approval-center.index'));
        $response->assertStatus(200);
        $response->assertViewHas('pendingFormulas', function ($formulas) {
            return $formulas->contains($this->pendingFormulaT1) && !$formulas->contains($this->pendingFormulaT2);
        });
        $response->assertViewHas('pendingTrialRms', function ($trials) {
            return $trials->contains($this->pendingTrialT1) && !$trials->contains($this->pendingTrialT2);
        });
        $response->assertViewHas('pendingTrialPms', function ($trials) {
            return $trials->contains($this->pendingTrialPm);
        });
    }

    public function test_gm_sees_only_pending_tahap_2_documents_in_approval_center()
    {
        $response = $this->actingAs($this->gm)->get(route('approval-center.index'));
        $response->assertStatus(200);
        $response->assertViewHas('pendingFormulas', function ($formulas) {
            return !$formulas->contains($this->pendingFormulaT1) && $formulas->contains($this->pendingFormulaT2);
        });
        $response->assertViewHas('pendingTrialRms', function ($trials) {
            return !$trials->contains($this->pendingTrialT1) && $trials->contains($this->pendingTrialT2);
        });
    }

    public function test_manager_can_approve_formula_tahap_1_promoting_to_tahap_2()
    {
        $response = $this->actingAs($this->manager)->post(route('approval-center.formulas.approve', $this->pendingFormulaT1));
        $this->assertEquals('Pending Tahap 2', $this->pendingFormulaT1->fresh()->approval_status);
        $this->assertEquals($this->manager->id, $this->pendingFormulaT1->fresh()->approved_by_om);
        $response->assertRedirect(route('approval-center.index'));
    }

    public function test_gm_can_approve_formula_tahap_2_promoting_to_approved_final()
    {
        $response = $this->actingAs($this->gm)->post(route('approval-center.formulas.approve', $this->pendingFormulaT2));
        $this->assertEquals('Approved', $this->pendingFormulaT2->fresh()->approval_status);
        $this->assertEquals($this->gm->id, $this->pendingFormulaT2->fresh()->approved_by_gm);
        $this->assertNotNull($this->pendingFormulaT2->fresh()->approved_at);
        $response->assertRedirect(route('approval-center.index'));
    }

    public function test_manager_can_reject_formula_with_notes()
    {
        $response = $this->actingAs($this->manager)->post(route('approval-center.formulas.reject', $this->pendingFormulaT1), [
            'rejection_notes' => 'Komposisi tidak stabil',
        ]);
        $this->assertEquals('Rejected', $this->pendingFormulaT1->fresh()->approval_status);
        $this->assertEquals('Komposisi tidak stabil', $this->pendingFormulaT1->fresh()->rejection_notes);
        $response->assertRedirect(route('approval-center.index'));
    }

    public function test_manager_can_approve_trial_rm_tahap_1_promoting_to_tahap_2()
    {
        $response = $this->actingAs($this->manager)->post(route('approval-center.trial-rms.approve', $this->pendingTrialT1));
        $this->assertEquals('Pending Tahap 2', $this->pendingTrialT1->fresh()->approval_status);
        $this->assertEquals($this->manager->id, $this->pendingTrialT1->fresh()->approved_by_om);
        $response->assertRedirect(route('approval-center.index'));
    }

    public function test_gm_can_approve_trial_rm_tahap_2_promoting_to_approved_final()
    {
        $response = $this->actingAs($this->gm)->post(route('approval-center.trial-rms.approve', $this->pendingTrialT2));
        $this->assertEquals('Approved', $this->pendingTrialT2->fresh()->approval_status);
        $this->assertEquals($this->gm->id, $this->pendingTrialT2->fresh()->approved_by_gm);
        $this->assertNotNull($this->pendingTrialT2->fresh()->approved_at);
        $response->assertRedirect(route('approval-center.index'));
    }

    public function test_manager_can_reject_trial_rm_with_notes()
    {
        $response = $this->actingAs($this->manager)->post(route('approval-center.trial-rms.reject', $this->pendingTrialT1), [
            'rejection_notes' => 'Uji viskositas tidak konsisten',
        ]);
        $this->assertEquals('Rejected', $this->pendingTrialT1->fresh()->approval_status);
        $this->assertEquals('Uji viskositas tidak konsisten', $this->pendingTrialT1->fresh()->rejection_notes);
        $response->assertRedirect(route('approval-center.index'));
    }

    public function test_staff_cannot_access_approval_center()
    {
        $response = $this->actingAs($this->staff)->get(route('approval-center.index'));
        $response->assertStatus(403);
    }

    public function test_manager_can_approve_trial_pm_final()
    {
        $response = $this->actingAs($this->manager)->post(route('approval-center.trial-pms.approve', $this->pendingTrialPm));
        $this->assertEquals('Approved', $this->pendingTrialPm->fresh()->approval_status);
        $this->assertEquals($this->manager->id, $this->pendingTrialPm->fresh()->approved_by_om);
        $this->assertNotNull($this->pendingTrialPm->fresh()->approved_at);
        $response->assertRedirect(route('approval-center.index'));
    }

    public function test_manager_can_reject_trial_pm_with_notes()
    {
        $response = $this->actingAs($this->manager)->post(route('approval-center.trial-pms.reject', $this->pendingTrialPm), [
            'rejection_notes' => 'Bahan tipis dan mudah bocor',
        ]);
        $this->assertEquals('Rejected', $this->pendingTrialPm->fresh()->approval_status);
        $this->assertEquals('Ditolak oleh Operational Manager: Bahan tipis dan mudah bocor', $this->pendingTrialPm->fresh()->rejection_notes);
        $response->assertRedirect(route('approval-center.index'));
    }

    public function test_gm_does_not_see_trial_pm_in_approval_center()
    {
        $response = $this->actingAs($this->gm)->get(route('approval-center.index'));
        $response->assertStatus(200);
        $response->assertViewHas('pendingTrialPms', function ($trials) {
            return !$trials->contains($this->pendingTrialPm);
        });
    }
}
