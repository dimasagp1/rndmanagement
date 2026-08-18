<?php

namespace Tests\Feature;

use App\Models\FormulaApprovalForm;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
                'product_id'      => $this->product->id,
                'kategori'        => 'Minuman',
                'komoditi'        => 'Rempah',
                'bentuk_sediaan'  => 'Serbuk Instan',
                'manufactured'    => 'PT Herbatech',
                'distributor'     => 'PT Distributor Sehat',
                'klaim_product'   => 'Menghangatkan tubuh',
                'komposisi'       => 'Jahe merah, gula aren',
                'aturan_pakai'    => '1 sachet, 2x sehari',
                'ukuran_kemasan'  => '10 x 15 gr',
                'packaging'       => 'Sachet aluminium foil',
                'sensory_product' => 'Pedas manis khas jahe',
                'target_launch'   => '2026-12-01',
            ])
            ->assertRedirect(route('formula-approvals.index'));

        $this->assertDatabaseHas('formula_approval_forms', [
            'product_id'   => $this->product->id,
            'product_name' => 'Jahe Merah Hangat',
            'kategori'     => 'Minuman',
        ]);
    }

    public function test_manager_tidak_bisa_isi_form_approval()
    {
        $this->actingAs($this->manager)
            ->post(route('formula-approvals.store'), [
                'product_id' => $this->product->id,
            ])
            ->assertForbidden();
    }

    public function test_produk_yang_sudah_punya_form_tidak_bisa_dipakai_lagi()
    {
        FormulaApprovalForm::create([
            'product_id'   => $this->product->id,
            'product_name' => 'Jahe Merah Hangat',
        ]);

        $this->actingAs($this->staff)
            ->post(route('formula-approvals.store'), [
                'product_id' => $this->product->id,
            ])
            ->assertSessionHasErrors('product_id');
    }

    public function test_alur_approval_om_lalu_gm()
    {
        $om = User::factory()->create();
        $om->assignRole('Operational Manager');
        $gm = User::factory()->create();
        $gm->assignRole('General Manager');

        $form = FormulaApprovalForm::create([
            'product_id'   => $this->product->id,
            'product_name' => 'Jahe Merah Hangat',
        ]);
        $form->refresh();

        $this->assertEquals('Pending', $form->approval_status);

        // OM belum setujui → GM tidak bisa setujui
        $this->actingAs($gm)
            ->post(route('formula-approvals.approve-gm', $form))
            ->assertStatus(422);

        // OM setujui
        $this->actingAs($om)
            ->post(route('formula-approvals.approve-om', $form))
            ->assertRedirect(route('formula-approvals.show', $form));
        $form->refresh();
        $this->assertEquals('Approval by OM', $form->approval_status);
        $this->assertEquals($om->id, $form->approved_by_om);

        // GM setujui
        $this->actingAs($gm)
            ->post(route('formula-approvals.approve-gm', $form))
            ->assertRedirect(route('formula-approvals.show', $form));
        $form->refresh();
        $this->assertEquals('Approved', $form->approval_status);
        $this->assertEquals($gm->id, $form->approved_by_gm);
        $this->assertNotNull($form->approved_at_gm);
    }

    public function test_om_dan_gm_bisa_tolak()
    {
        $om = User::factory()->create();
        $om->assignRole('Operational Manager');

        $form = FormulaApprovalForm::create([
            'product_id'   => $this->product->id,
            'product_name' => 'Jahe Merah Hangat',
        ]);

        $this->actingAs($om)
            ->post(route('formula-approvals.reject', $form), [
                'rejection_notes' => 'Komposisi belum memenuhi standar mutu.',
            ])
            ->assertRedirect(route('formula-approvals.show', $form));

        $form->refresh();
        $this->assertEquals('Rejected', $form->approval_status);
        $this->assertEquals('Komposisi belum memenuhi standar mutu.', $form->rejection_notes);
    }
}