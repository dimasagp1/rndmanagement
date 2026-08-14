<?php

namespace Tests\Feature;

use App\Models\NpdProposal;
use App\Models\Prf;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NpdProposalSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_http_flow(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $staff = User::factory()->create(['email' => 'npd-staff@test.com']);
        $staffPackdev = User::factory()->create(['email' => 'npd-packdev@test.com', 'name' => 'Emir Packdev']);
        $om = User::factory()->create(['email' => 'npd-om@test.com']);
        $gm = User::factory()->create(['email' => 'npd-gm@test.com', 'name' => 'Lisa GM']);
        $superadmin = User::factory()->create(['email' => 'npd-sa@test.com', 'name' => 'Admin SA']);
        $staff->assignRole('Staff R&D');
        $staffPackdev->assignRole('Staff Packdev');
        $om->assignRole('Operational Manager');
        $gm->assignRole('General Manager');
        $superadmin->assignRole('Superadmin');

        $prf = Prf::create([
            'code' => 'PRF-SMOKE',
            'product_concept' => 'Konsep', 'product_name' => 'Produk X',
            'approval_status' => 'Submitted', 'created_by' => $staff->id,
        ]);

        // Redirect general/npd-proposal -> module
        $this->actingAs($staff)->get('/general/npd-proposal')->assertRedirect('/npd-proposals');

        // PRF created via HTTP is directly Submitted (no submit button needed)
        $this->actingAs($staff)->post('/prfs', [
            'code' => 'PRF-AUTO', 'product_concept' => 'Konsep otomatis',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('prfs', ['code' => 'PRF-AUTO', 'approval_status' => 'Submitted']);

        // Create page renders; PIC select only shows Staff R&D / Staff Packdev users
        $this->actingAs($staff)->get('/npd-proposals/create')
            ->assertOk()
            ->assertSee('PRF-SMOKE')
            ->assertSee('Emir Packdev')
            ->assertSee($staff->name)
            ->assertDontSee('Lisa GM')
            ->assertDontSee('Admin SA')
            ->assertDontSee('Sementara diisi manual');

        // Store
        $this->actingAs($staff)->post('/npd-proposals', [
            'code' => 'NPD-SMOKE', 'prf_id' => $prf->id,
            'product_name' => 'Produk X', 'product_concept' => 'Konsep',
            'target_cogs' => 15000, 'target_selling_price' => 25000,
            'pic' => $staff->name, 'development_start' => '2026-08-14', 'development_end' => '2026-12-31',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('npd_proposals', ['code' => 'NPD-SMOKE', 'project_status' => 'Draft']);
        $proposal = NpdProposal::where('code', 'NPD-SMOKE')->first();

        // Old approval routes no longer exist
        $this->get("/npd-proposals/{$proposal->id}/submit")->assertNotFound();
        $this->get("/npd-proposals/{$proposal->id}/approve-tahap1")->assertNotFound();

        // Project status update (creator only)
        $this->actingAs($staff)->post("/npd-proposals/{$proposal->id}/project-status", ['project_status' => 'On Track'])->assertRedirect();
        $this->assertDatabaseHas('npd_proposals', ['code' => 'NPD-SMOKE', 'project_status' => 'On Track']);

        // Edit page: PIC lama yang tidak ada di daftar tim tampil sebagai opsi fallback
        $proposal->update(['pic' => 'Nama Lama Tidak Ada']);
        $this->actingAs($staff)->get("/npd-proposals/{$proposal->id}/edit")
            ->assertOk()
            ->assertSee('Nama Lama Tidak Ada (PIC lama)');

        // PRF used by an NPD Proposal cannot be deleted
        $this->actingAs($staff)->delete("/prfs/{$prf->id}")->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('prfs', ['code' => 'PRF-SMOKE']);

        // Approval center no longer shows NPD tab
        $this->actingAs($om)->get('/approval-center')
            ->assertOk()
            ->assertDontSee("activeTab === 'npd_proposals'");
    }
}
