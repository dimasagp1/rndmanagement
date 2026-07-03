<?php

namespace Tests\Feature;

use App\Models\Formula;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\User;
use App\Models\TrialRm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrialRmTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $manager;
    private Formula $approvedFormula;
    private Formula $draftFormula;

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

        // Create Formulas
        $this->approvedFormula = Formula::create([
            'code'              => 'FRM-202607-001',
            'name'              => 'Formula Approved',
            'version'           => 1,
            'development_stage' => 'Final',
            'approval_status'   => 'Approved',
            'created_by'        => $this->staff->id,
        ]);

        $this->draftFormula = Formula::create([
            'code'              => 'FRM-202607-002',
            'name'              => 'Formula Draft',
            'version'           => 1,
            'development_stage' => 'Draf',
            'approval_status'   => 'Draft',
            'created_by'        => $this->staff->id,
        ]);
    }

    public function test_staff_rnd_can_create_trial_rm_for_approved_formula()
    {
        $response = $this->actingAs($this->staff)->post(route('trial-rms.store'), [
            'code'            => 'TRM-202607-001-A',
            'formula_id'      => $this->approvedFormula->id,
            'sample_identity' => 'Sample Batch 1',
            'process_steps'   => 'Langkah 1, Langkah 2',
            'decision'        => 'Lulus',
            'verifications'   => [
                [
                    'parameter_name' => 'Warna',
                    'target_value'   => 'Kuning',
                    'actual_value'   => 'Kuning',
                    'status'         => 'Pass',
                ],
            ],
        ]);

        $trial = TrialRm::first();
        $this->assertNotNull($trial);
        $this->assertEquals('Sample Batch 1', $trial->sample_identity);
        $this->assertEquals('Lulus', $trial->decision);
        $this->assertEquals('Draft', $trial->approval_status);
        $this->assertEquals('TRM-202607-001-A', $trial->code);

        $response->assertRedirect(route('trial-rms.show', $trial));
    }

    public function test_cannot_create_trial_for_unapproved_formula()
    {
        $response = $this->actingAs($this->staff)->post(route('trial-rms.store'), [
            'code'            => 'TRM-202607-999-A',
            'formula_id'      => $this->draftFormula->id,
            'sample_identity' => 'Sample Batch Unapproved',
            'process_steps'   => 'Langkah 1',
            'verifications'   => [],
        ]);

        $response->assertSessionHasErrors(['formula_id']);
        $this->assertEquals(0, TrialRm::count());
    }

    public function test_repeated_trial_for_same_formula_stores_manually_inputted_code()
    {
        // First trial
        $trial1 = TrialRm::create([
            'code'            => 'TRM-202607-001-A',
            'formula_id'      => $this->approvedFormula->id,
            'sample_identity' => 'Batch 1',
            'process_steps'   => 'Process 1',
            'approval_status' => 'Draft',
            'created_by'      => $this->staff->id,
        ]);

        // Second trial store request with custom code
        $response = $this->actingAs($this->staff)->post(route('trial-rms.store'), [
            'code'            => 'CUSTOM-TRIAL-CODE-02',
            'formula_id'      => $this->approvedFormula->id,
            'sample_identity' => 'Batch 2',
            'process_steps'   => 'Process 2',
            'verifications'   => [],
        ]);

        $trial2 = TrialRm::where('sample_identity', 'Batch 2')->first();
        $this->assertNotNull($trial2);
        $this->assertEquals('CUSTOM-TRIAL-CODE-02', $trial2->code);
    }

    public function test_manager_cannot_edit_trial_rm()
    {
        $trial = TrialRm::create([
            'code'            => 'TRM-202607-001-A',
            'formula_id'      => $this->approvedFormula->id,
            'sample_identity' => 'Batch 1',
            'process_steps'   => 'Process 1',
            'approval_status' => 'Draft',
            'created_by'      => $this->staff->id,
        ]);

        $response = $this->actingAs($this->manager)->get(route('trial-rms.edit', $trial));
        $response->assertStatus(403);
    }
}
