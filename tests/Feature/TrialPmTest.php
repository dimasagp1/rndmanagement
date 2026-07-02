<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TrialPm;
use App\Models\TrialPmApproval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrialPmTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $verifier;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Roles and Permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // Create Users
        $this->staff = User::factory()->create();
        $this->staff->assignRole('Staff R&D');

        $this->verifier = User::factory()->create();
        $this->verifier->assignRole('Staff R&D'); // Staff R&D has trial_pm.department_approve permission
    }

    public function test_staff_rnd_can_create_trial_pm_and_initializes_4_department_approvals()
    {
        $response = $this->actingAs($this->staff)->post(route('trial-pms.store'), [
            'packaging_material' => 'Botol PET 250ml',
            'specifications'     => 'Spesifikasi detail',
            'parameters'         => [
                'kecepatan_filling' => '80 botol/menit',
                'suhu_sealing'      => '180°C',
                'tekanan_mesin'     => '4.5 bar',
            ],
            'risk_analysis'      => 'Resiko bocor',
        ]);

        $trial = TrialPm::first();
        $this->assertNotNull($trial);
        $this->assertEquals('Botol PET 250ml', $trial->packaging_material);
        $this->assertEquals('Draft', $trial->approval_status);
        $this->assertStringStartsWith('TPM-' . now()->format('Ym') . '-', $trial->code);

        // Verify 4 department approvals initialized
        $approvals = TrialPmApproval::where('trial_pm_id', $trial->id)->get();
        $this->assertCount(4, $approvals);
        $this->assertEqualsCanonicalizing(['rd', 'qc', 'production', 'engineering'], $approvals->pluck('department')->toArray());
        $this->assertFalse($approvals->first()->is_approved);

        $response->assertRedirect(route('trial-pms.show', $trial));
    }

    public function test_all_departments_approving_auto_promotes_trial_pm_to_approved()
    {
        $trial = TrialPm::create([
            'code'               => 'TPM-202607-001',
            'packaging_material' => 'Sachet alu',
            'specifications'     => 'Foil',
            'approval_status'    => 'Draft',
            'created_by'         => $this->staff->id,
        ]);

        // Initialize 4 approvals
        $depts = ['rd', 'qc', 'production', 'engineering'];
        foreach ($depts as $dept) {
            TrialPmApproval::create([
                'trial_pm_id' => $trial->id,
                'department'  => $dept,
                'is_approved' => false,
            ]);
        }

        // Submit for review
        $this->actingAs($this->staff)->post(route('trial-pms.submit', $trial));
        $this->assertEquals('Pending Review', $trial->fresh()->approval_status);

        // Approve 3 departments first
        foreach (['rd', 'qc', 'production'] as $dept) {
            $this->actingAs($this->verifier)->post(route('trial-pms.approve', $trial), [
                'department'  => $dept,
                'is_approved' => true,
                'notes'       => 'Sesuai',
            ]);
            $this->assertEquals('Pending Review', $trial->fresh()->approval_status);
        }

        // Approve the last department (engineering)
        $this->actingAs($this->verifier)->post(route('trial-pms.approve', $trial), [
            'department'  => 'engineering',
            'is_approved' => true,
            'notes'       => 'Sesuai',
        ]);

        // Status should automatically become Approved
        $this->assertEquals('Approved', $trial->fresh()->approval_status);
        $this->assertNotNull($trial->fresh()->approved_at);
    }

    public function test_any_department_rejecting_auto_sets_trial_pm_to_rejected()
    {
        $trial = TrialPm::create([
            'code'               => 'TPM-202607-001',
            'packaging_material' => 'Sachet alu',
            'specifications'     => 'Foil',
            'approval_status'    => 'Draft',
            'created_by'         => $this->staff->id,
        ]);

        $depts = ['rd', 'qc', 'production', 'engineering'];
        foreach ($depts as $dept) {
            TrialPmApproval::create([
                'trial_pm_id' => $trial->id,
                'department'  => $dept,
                'is_approved' => false,
            ]);
        }

        $this->actingAs($this->staff)->post(route('trial-pms.submit', $trial));

        // One department rejects
        $response = $this->actingAs($this->verifier)->post(route('trial-pms.approve', $trial), [
            'department'  => 'rd',
            'is_approved' => false,
            'notes'       => 'Bahan foil bocor',
        ]);

        // Status should automatically become Rejected
        $this->assertEquals('Rejected', $trial->fresh()->approval_status);
        $this->assertStringContainsString('Ditolak oleh departemen RD: Bahan foil bocor', $trial->fresh()->rejection_notes);
    }
}
