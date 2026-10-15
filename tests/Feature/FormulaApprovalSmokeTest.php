<?php

namespace Tests\Feature;

use App\Models\FormulaApprovalForm;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormulaApprovalSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $manager;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->staff = User::factory()->create();
        $this->staff->assignRole('Staff R&D');

        $this->manager = User::factory()->create();
        $this->manager->assignRole('Operational Manager');

        $this->product = Product::create(['name' => 'Jahe Merah Hangat']);
        Product::create(['name' => 'Produk Lain']);

        ProductCategory::create(['name' => 'COD']);
        ProductCategory::create(['name' => 'COL']);
        ProductCategory::create(['name' => 'Serbuk']);
        ProductCategory::create(['name' => 'Kapsul']);
    }

    public function test_index_menampilkan_hanya_produk_yang_punya_form()
    {
        // Belum punya form → tidak tampil
        $this->actingAs($this->staff)
            ->get(route('formula-approvals.index'))
            ->assertOk()
            ->assertDontSee('Jahe Merah Hangat');

        // Setelah punya form → tampil
        FormulaApprovalForm::create([
            'product_id'    => $this->product->id,
            'product_name'  => 'Jahe Merah Hangat',
            'kategori'      => 'Minuman',
            'komoditi'      => 'Rempah',
            'bentuk_sediaan' => 'Serbuk Instan',
            'manufactured'  => 'PT Herbatech',
            'distributor'   => 'PT Distributor Sehat',
            'klaim_product' => 'Menghangatkan tubuh',
            'komposisi'     => 'Jahe merah, gula aren',
            'aturan_pakai'  => '1 sachet, 2x sehari',
            'ukuran_kemasan' => '10 x 15 gr',
            'packaging'     => 'Sachet aluminium foil',
            'sensory_product' => 'Pedas manis khas jahe',
            'target_launch' => '2026-12-01',
        ]);

        $this->actingAs($this->staff)
            ->get(route('formula-approvals.index'))
            ->assertOk()
            ->assertSee('Jahe Merah Hangat')
            ->assertDontSee('Produk Lain');
    }

    public function test_staff_dapat_isi_form_approval()
    {
        $this->actingAs($this->staff)
            ->post(route('formula-approvals.store'), [
                'type'            => 'Formula',
                'product_name'    => 'Jahe Merah Hangat',
                'kategori'        => 'New Product',
                'komoditi'        => 'Rempah',
                'bentuk_sediaan'  => 'Serbuk',
                'manufactured'    => 'PT Herbatech',
                'klaim_product'   => 'Menghangatkan tubuh',
                'komposisi'       => 'Jahe merah, gula aren',
                'aturan_pakai'    => '1 sachet, 2x sehari',
                'packaging'       => 'Sachet aluminium foil',
                'sensory_product' => 'Pedas manis khas jahe',
                'target_launch'   => '2026-12-01',
            ])
            ->assertRedirect(route('formula-approvals.index', ['type' => 'Formula']));

        $this->assertDatabaseHas('formula_approval_forms', [
            'product_name'    => 'Jahe Merah Hangat',
            'product_id'      => null,
            'kategori'        => 'New Product',
            'bentuk_sediaan'  => 'Serbuk',
            'approval_status' => 'Draft',
        ]);
    }

    public function test_manager_tidak_bisa_isi_form_approval()
    {
        $this->actingAs($this->manager)
            ->post(route('formula-approvals.store'), [
                'type'         => 'Formula',
                'product_name' => 'Jahe Merah Hangat',
            ])
            ->assertForbidden();
    }

    public function test_nama_produk_dan_kategori_wajib_diisi()
    {
        $this->actingAs($this->staff)
            ->post(route('formula-approvals.store'), [
                'type'     => 'Formula',
                'kategori' => 'New Product',
            ])
            ->assertSessionHasErrors('product_name');

        $this->actingAs($this->staff)
            ->post(route('formula-approvals.store'), [
                'type'         => 'Formula',
                'product_name' => 'Jahe Merah Hangat',
            ])
            ->assertSessionHasErrors('kategori');
    }

    public function test_bentuk_sediaan_harus_dari_menu_kategori()
    {
        $this->actingAs($this->staff)
            ->post(route('formula-approvals.store'), [
                'type'           => 'Formula',
                'product_name'   => 'Jahe Merah Hangat',
                'kategori'       => 'New Product',
                'bentuk_sediaan' => 'Sirup Instan',
            ])
            ->assertSessionHasErrors('bentuk_sediaan');
    }

    public function test_staff_dapat_upload_lampiran_saat_tambah_form_dan_hapusnya()
    {
        Storage::fake('public');

        $this->actingAs($this->staff)
            ->post(route('formula-approvals.store'), [
                'type'         => 'Formula',
                'product_name' => 'Jahe Merah Hangat',
                'kategori'     => 'New Product',
                'files'        => [
                    UploadedFile::fake()->create('spesifikasi.pdf', 200, 'application/pdf'),
                    UploadedFile::fake()->create('memo.docx', 150, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
                ],
            ])
            ->assertRedirect(route('formula-approvals.index', ['type' => 'Formula']));

        $form = FormulaApprovalForm::where('product_name', 'Jahe Merah Hangat')->firstOrFail();

        $this->assertDatabaseHas('formula_approval_attachments', [
            'formula_approval_id' => $form->id,
            'original_name'       => 'spesifikasi.pdf',
        ]);
        $this->assertDatabaseHas('formula_approval_attachments', [
            'formula_approval_id' => $form->id,
            'original_name'       => 'memo.docx',
        ]);

        $attachment = $form->attachments()->first();

        $this->actingAs($this->staff)
            ->delete(route('formula-approvals.attachments.destroy', [$form, $attachment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('formula_approval_attachments', ['id' => $attachment->id]);
    }

    public function test_alur_approval_hanya_oleh_gm()
    {
        $gm = User::factory()->create();
        $gm->assignRole('General Manager');

        $form = FormulaApprovalForm::create([
            'type'            => 'Formula',
            'product_id'      => $this->product->id,
            'product_name'    => 'Jahe Merah Hangat',
            'approval_status' => 'Pending',
        ]);

        // Tahap OM sudah dihapus
        $this->actingAs($this->manager)
            ->post(route('formula-approvals.approve-om', $form))
            ->assertStatus(422);
        $this->actingAs($this->manager)
            ->post(route('formula-approvals.approve-gm', $form))
            ->assertForbidden();

        $this->actingAs($gm)
            ->post(route('formula-approvals.approve-gm', $form))
            ->assertRedirect(route('formula-approvals.show', ['formApproval' => $form, 'type' => 'Formula']));

        $form->refresh();
        $this->assertEquals('Approved', $form->approval_status);
        $this->assertEquals($gm->id, $form->approved_by_gm);
        $this->assertNotNull($form->approved_at_gm);
    }

    public function test_hanya_gm_yang_bisa_menolak()
    {
        $gm = User::factory()->create();
        $gm->assignRole('General Manager');

        $form = FormulaApprovalForm::create([
            'type'            => 'Formula',
            'product_id'      => $this->product->id,
            'product_name'    => 'Jahe Merah Hangat',
            'approval_status' => 'Pending',
        ]);

        $this->actingAs($this->manager)
            ->post(route('formula-approvals.reject', $form), ['rejection_notes' => 'Tidak sesuai.'])
            ->assertForbidden();

        $this->actingAs($gm)
            ->post(route('formula-approvals.reject', $form), [
                'rejection_notes' => 'Komposisi belum memenuhi standar mutu.',
            ])
            ->assertRedirect(route('formula-approvals.show', ['formApproval' => $form, 'type' => 'Formula']));

        $form->refresh();
        $this->assertEquals('Rejected', $form->approval_status);
        $this->assertEquals('Komposisi belum memenuhi standar mutu.', $form->rejection_notes);
    }
}
