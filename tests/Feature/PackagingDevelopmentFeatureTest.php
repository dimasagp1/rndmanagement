<?php

namespace Tests\Feature;

use App\Models\PackagingCompatibilityEvaluation;
use App\Models\PackagingDevelopment;
use App\Models\PackagingTrial;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PackagingDevelopmentFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $manager;
    private User $gm;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->staff = User::factory()->create();
        $this->staff->assignRole('Staff R&D');

        $this->manager = User::factory()->create();
        $this->manager->assignRole('Operational Manager');

        $this->gm = User::factory()->create();
        $this->gm->assignRole('General Manager');

        $this->product = Product::create(['name' => 'Jahe Merah Hangat']);
        ProductCategory::create(['name' => 'Serbuk']);
    }

    private function createDevelopment(array $overrides = []): PackagingDevelopment
    {
        return PackagingDevelopment::create([
            'product_id'          => $this->product->id,
            'product_name'        => 'Jahe Merah Hangat',
            'product_category'    => 'Serbuk',
            'packaging_type'      => 'Sachet',
            'development_purpose' => 'New Product Development',
            'target_launch'       => now()->addMonths(2)->format('Y-m-d'),
            'approval_status'     => 'Draft',
            'development_stage'   => 'Draft',
            'revision'            => 0,
            'created_by'          => $this->staff->id,
            ...$overrides,
        ]);
    }

    public function test_staff_dapat_membuka_halaman_create()
    {
        $this->actingAs($this->staff)
            ->get(route('packaging-developments.create'))
            ->assertOk()
            ->assertSee('Jahe Merah Hangat')
            ->assertSee('Serbuk');
    }

    public function test_staff_dapat_membuat_packaging_development()
    {
        $this->actingAs($this->staff)
            ->post(route('packaging-developments.store'), [
                'product_id'          => $this->product->id,
                'product_category'    => 'Serbuk',
                'packaging_type'      => 'Sachet',
                'development_purpose' => 'New Product Development',
                'target_launch'       => now()->addMonths(2)->format('Y-m-d'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('packaging_developments', [
            'product_id'       => $this->product->id,
            'product_name'     => 'Jahe Merah Hangat',
            'packaging_type'   => 'Sachet',
            'approval_status'  => 'Draft',
            'development_stage'=> 'Draft',
            'created_by'       => $this->staff->id,
        ]);

        $this->assertDatabaseHas('packaging_audit_logs', [
            'action' => 'Membuat Packaging Development',
        ]);
    }

    public function test_manager_tidak_bisa_membuat_packaging_development()
    {
        $this->actingAs($this->manager)
            ->post(route('packaging-developments.store'), [
                'product_id'          => $this->product->id,
                'product_category'    => 'Serbuk',
                'packaging_type'      => 'Sachet',
                'development_purpose' => 'New Product Development',
                'target_launch'       => now()->addMonths(2)->format('Y-m-d'),
            ])
            ->assertForbidden();
    }

    public function test_alur_approval_om_lalu_gm()
    {
        $dev = $this->createDevelopment();

        // GM belum bisa approve sebelum OM
        $this->actingAs($this->gm)
            ->post(route('packaging-developments.approve-gm', $dev))
            ->assertStatus(422);

        // Staff submit
        $this->actingAs($this->staff)
            ->post(route('packaging-developments.submit', $dev))
            ->assertRedirect(route('packaging-developments.show', $dev));
        $dev->refresh();
        $this->assertEquals('Pending OM', $dev->approval_status);
        $this->assertEquals('In Review', $dev->development_stage);
        $this->assertNotNull($dev->submitted_at);

        // Staff tidak bisa approve
        $this->actingAs($this->staff)
            ->post(route('packaging-developments.approve-om', $dev))
            ->assertForbidden();

        // OM approve
        $this->actingAs($this->manager)
            ->post(route('packaging-developments.approve-om', $dev), ['comment' => 'Spesifikasi sesuai.'])
            ->assertRedirect(route('packaging-developments.show', $dev));
        $dev->refresh();
        $this->assertEquals('Pending GM', $dev->approval_status);
        $this->assertEquals($this->manager->id, $dev->approved_by_om);

        $this->assertDatabaseHas('packaging_approvals', [
            'packaging_development_id' => $dev->id,
            'step'                     => 'OM Approval',
            'status'                   => 'Approved',
        ]);

        // GM approve final
        $this->actingAs($this->gm)
            ->post(route('packaging-developments.approve-gm', $dev))
            ->assertRedirect(route('packaging-developments.show', $dev));
        $dev->refresh();
        $this->assertEquals('Approved', $dev->approval_status);
        $this->assertEquals('Approved', $dev->development_stage);
        $this->assertEquals($this->gm->id, $dev->approved_by_gm);
        $this->assertEquals(1, $dev->revisions()->count());
    }

    public function test_staff_dapat_mengisi_spesifikasi_primary_dan_secondary()
    {
        $dev = $this->createDevelopment();

        $this->actingAs($this->staff)
            ->post(route('packaging-developments.specifications.save', $dev), [
                'packaging_type'     => 'Sachet',
                'dimension'          => '100 × 150 mm',
                'material_structure' => 'PET/AL/PE',
            ])
            ->assertRedirect();

        $this->actingAs($this->staff)
            ->post(route('packaging-developments.primary.save', $dev), [
                'packaging_type'      => 'Sachet',
                'material'            => 'PET/AL/PE',
                'product_contact'     => 'Yes',
                'light_protection'    => 'Yes',
                'moisture_protection' => 'Yes',
                'oxygen_protection'   => 'Yes',
            ])
            ->assertRedirect();

        $this->actingAs($this->staff)
            ->post(route('packaging-developments.secondary.save', $dev), [
                'packaging_type' => 'Folding Box',
            ])
            ->assertRedirect();

        $dev->refresh();

        $this->assertNotNull($dev->specification);
        $this->assertEquals('PET/AL/PE', $dev->specification->material_structure);
        $this->assertNotNull($dev->primaryPackaging);
        $this->assertEquals('Yes', $dev->primaryPackaging->product_contact);
        $this->assertNotNull($dev->secondaryPackaging);
    }

    public function test_trial_fail_wajib_isi_failure_reason()
    {
        $dev = $this->createDevelopment();

        $this->actingAs($this->staff)
            ->post(route('packaging-developments.trials.store', $dev), [
                'trial_date'         => now()->format('Y-m-d'),
                'packaging_material' => 'PET/AL/PE',
                'trial_purpose'      => 'Seal Optimization',
                'result'             => 'Fail',
                'retest_required'    => 'No',
            ])
            ->assertSessionHasErrors('failure_reason');
    }

    public function test_staff_dapat_mencatat_trial_pass_dengan_parameter()
    {
        $dev = $this->createDevelopment();

        $this->actingAs($this->staff)
            ->post(route('packaging-developments.trials.store', $dev), [
                'trial_date'         => now()->format('Y-m-d'),
                'trial_batch'        => 'BATCH-001',
                'packaging_material' => 'PET/AL/PE',
                'machine'            => 'Packing Machine 01',
                'quantity'           => '10,000 pcs',
                'trial_purpose'      => 'Seal Optimization',
                'result'             => 'Pass',
                'retest_required'    => 'No',
            ])
            ->assertRedirect();

        $trial = $dev->trials()->first();
        $this->assertNotNull($trial);
        $this->assertEquals('TRIAL-PKG-001', $trial->trial_no);

        $this->actingAs($this->staff)
            ->post(route('packaging-developments.trials.parameters.store', [$dev, $trial]), [
                'parameter' => 'Sealing Temperature',
                'target'    => '160°C',
                'actual'    => '158°C',
                'result'    => 'Pass',
            ])
            ->assertRedirect();

        $this->assertEquals(1, $trial->parameters()->count());
    }

    public function test_compatibility_fail_wajib_isi_finding_dan_action()
    {
        $dev = $this->createDevelopment();

        $this->actingAs($this->staff)
            ->post(route('packaging-developments.compatibilities.store', $dev), [
                'evaluation_date'   => now()->format('Y-m-d'),
                'evaluation_method' => 'Internal Test Method',
                'test_condition'    => 'Room Temperature',
                'result'            => 'Fail',
            ])
            ->assertSessionHasErrors(['finding', 'corrective_action']);
    }

    public function test_staff_dapat_mencatat_supplier_dan_material()
    {
        $dev = $this->createDevelopment();

        $this->actingAs($this->staff)
            ->post(route('packaging-developments.suppliers.store', $dev), [
                'supplier_name'        => 'PT ABC Packaging',
                'qualification_status' => 'Qualified',
                'supplier_status'      => 'Active',
                'audit_status'         => 'Passed',
            ])
            ->assertRedirect();

        $this->actingAs($this->staff)
            ->post(route('packaging-developments.materials.store', $dev), [
                'material_name'     => 'PET/AL/PE',
                'current_material'  => 'PET/PE',
                'proposed_material' => 'PET/AL/PE',
                'reason_for_change' => 'Meningkatkan barrier.',
                'expected_benefit'  => 'Shelf life lebih panjang.',
                'risk'              => 'Low',
            ])
            ->assertRedirect();

        $this->assertEquals(1, $dev->suppliers()->count());
        $this->assertEquals(1, $dev->materialDevelopments()->count());
    }

    public function test_om_bisa_tolak_dengan_catatan()
    {
        $dev = $this->createDevelopment(['approval_status' => 'Pending OM', 'submitted_at' => now()]);

        $this->actingAs($this->manager)
            ->post(route('packaging-developments.reject', $dev), [
                'rejection_notes' => 'Spesifikasi belum lengkap.',
            ])
            ->assertRedirect(route('packaging-developments.show', $dev));

        $dev->refresh();
        $this->assertEquals('Rejected', $dev->approval_status);
        $this->assertEquals('Spesifikasi belum lengkap.', $dev->rejection_notes);
    }

    public function test_data_approved_tidak_bisa_diedit_langsung_tapi_bisa_duplicate()
    {
        $dev = $this->createDevelopment(['approval_status' => 'Approved']);

        // Edit diblokir
        $this->actingAs($this->staff)
            ->get(route('packaging-developments.edit', $dev))
            ->assertForbidden();

        // Duplicate membuat revisi baru Draft
        $this->actingAs($this->staff)
            ->post(route('packaging-developments.duplicate', $dev), [
                'change_description' => 'Perubahan dimensi kemasan.',
            ])
            ->assertRedirect();

        $copy = PackagingDevelopment::where('approval_status', 'Draft')->first();
        $this->assertNotNull($copy);
        $this->assertEquals(1, $copy->revision);
        $this->assertNotEquals($dev->id, $copy->id);
        $this->assertDatabaseHas('packaging_revisions', [
            'packaging_development_id' => $copy->id,
            'revision'                 => 'Rev 01',
        ]);
    }

    public function test_upload_dokumen_pdf_diterima_dan_png_ditolak()
    {
        Storage::fake('public');

        $dev = $this->createDevelopment();

        $this->actingAs($this->staff)
            ->post(route('packaging-developments.attachments.store', $dev), [
                'document_name' => 'Packaging Specification Rev.01',
                'document_type' => 'Packaging Specification',
                'revision'      => 'Rev.01',
                'file'          => UploadedFile::fake()->create('spec.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('packaging_attachments', [
            'packaging_development_id' => $dev->id,
            'document_name'            => 'Packaging Specification Rev.01',
            'original_name'            => 'spec.pdf',
        ]);

        $this->actingAs($this->staff)
            ->post(route('packaging-developments.attachments.store', $dev), [
                'document_name' => 'Gambar',
                'document_type' => 'Packaging Artwork',
                'revision'      => 'Rev.01',
                'file'          => UploadedFile::fake()->create('gambar.exe', 100, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors('file');
    }

    public function test_approval_center_menampilkan_antrean_packaging_per_role()
    {
        $this->createDevelopment([
            'product_id'     => $this->product->id,
            'product_name'   => 'Jahe Merah Hangat',
            'approval_status'=> 'Pending OM',
            'submitted_at'   => now(),
        ]);

        $this->createDevelopment([
            'approval_status' => 'Pending GM',
            'submitted_at'    => now(),
        ]);

        // OM hanya melihat antrean Pending OM
        $this->actingAs($this->manager)
            ->get(route('approval-center.index'))
            ->assertOk()
            ->assertSee('Jahe Merah Hangat');

        // GM melihat antrean Pending GM
        $this->actingAs($this->gm)
            ->get(route('approval-center.index'))
            ->assertOk()
            ->assertSee('Jahe Merah Hangat');
    }

    public function test_staff_dapat_mengubah_development_stage()
    {
        $dev = $this->createDevelopment();

        $this->actingAs($this->staff)
            ->post(route('packaging-developments.stage', $dev), [
                'development_stage' => 'Packaging Trial',
            ])
            ->assertRedirect();

        $dev->refresh();
        $this->assertEquals('Packaging Trial', $dev->development_stage);
        $this->assertDatabaseHas('packaging_audit_logs', [
            'packaging_development_id' => $dev->id,
            'action'                   => 'Mengubah Stage',
        ]);
    }
}