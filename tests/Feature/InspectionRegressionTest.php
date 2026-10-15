<?php

namespace Tests\Feature;

use App\Models\Formula;
use App\Models\FormulaApprovalForm;
use App\Models\LogbookPm;
use App\Models\Material;
use App\Models\PreformulationStudy;
use App\Models\Product;
use App\Models\TrialRm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi untuk temuan inspeksi kode (lihat LAPORAN_INSPEKSI_KODE.md).
 */
class InspectionRegressionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $otherStaff;
    private User $manager;
    private User $gm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->staff = User::factory()->create();
        $this->staff->assignRole('Staff R&D');

        $this->otherStaff = User::factory()->create();
        $this->otherStaff->assignRole('Staff R&D');

        $this->manager = User::factory()->create();
        $this->manager->assignRole('Operational Manager');

        $this->gm = User::factory()->create();
        $this->gm->assignRole('General Manager');
    }

    // ── Tahap 1 ─────────────────────────────────────────────────────

    // K-01
    public function test_preformulation_study_detail_dapat_dibuka(): void
    {
        $study = PreformulationStudy::create([
            'code'            => 'PRE-202610-001',
            'product_name'    => 'Produk Uji',
            'study_type'      => 'Study Preform',
            'status'          => 'Draft',
            'approval_status' => 'Draft',
            'created_by'      => $this->staff->id,
        ]);

        $this->actingAs($this->staff)
            ->get(route('preformulation-studies.show', $study))
            ->assertOk();
    }

    // K-02
    public function test_halaman_detail_data_master_tidak_error_500(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Superadmin');
        $material = Material::create(['name' => 'Bahan Uji', 'unit' => 'kg']);

        $this->actingAs($admin)->get('/users/' . $this->staff->id)->assertStatus(405);
        $this->actingAs($admin)->get('/materials/' . $material->id)->assertStatus(405);
    }

    // K-03
    public function test_formula_tanpa_supplier_ditolak_dengan_pesan_validasi(): void
    {
        $product = Product::create(['name' => 'Produk Uji']);
        $material = Material::create(['name' => 'Bahan Uji', 'unit' => 'kg']);

        $this->actingAs($this->staff)
            ->post(route('formulas.store'), [
                'code'              => 'FRM-202610-901',
                'product_id'        => $product->id,
                'development_stage' => 'Product Form',
                'materials'         => [['material_id' => $material->id, 'supplier_id' => '', 'percentage' => 50]],
            ])
            ->assertSessionHasErrors('materials.0.supplier_id');

        $this->assertDatabaseMissing('formulas', ['code' => 'FRM-202610-901']);
    }

    // ── Tahap 2 ─────────────────────────────────────────────────────

    // K-04
    public function test_staff_tidak_bisa_menyetujui_log_book_sendiri(): void
    {
        $this->actingAs($this->staff)
            ->post(route('logbook-pm.store'), [
                'tanggal_terima'       => '2026-10-01',
                'jenis_kemasan'        => 'Botol',
                'nama_material'        => 'Botol PET',
                'jumlah_diterima'      => 10,
                'satuan'               => 'pcs',
                'kelengkapan_dokumen'  => 'Lengkap',
                'kondisi_fisik_aktual' => 'Baik',
                'status_pengujian'     => 'Pending',
                'nama_penerima'        => 'Penerima',
                'om_approval'          => 'Approved',
            ])
            ->assertRedirect();

        $this->assertSame('Pending', LogbookPm::firstOrFail()->om_approval);
    }

    // K-08
    public function test_om_tidak_bisa_menolak_formula_tahap_gm(): void
    {
        $formula = $this->makeFormula('Pending Tahap 2');

        $this->actingAs($this->manager)
            ->post(route('approval-center.formulas.reject', $formula), ['rejection_notes' => 'Tidak sesuai'])
            ->assertForbidden();

        $this->assertSame('Pending Tahap 2', $formula->fresh()->approval_status);
    }

    public function test_gm_tidak_bisa_menolak_trial_rm_tahap_om(): void
    {
        $trial = TrialRm::create([
            'code'            => 'TRM-202610-001-A',
            'formula_id'      => $this->makeFormula('Approved')->id,
            'sample_identity' => 'Sampel Uji',
            'process_steps'   => 'Langkah',
            'approval_status' => 'Pending Tahap 1',
            'created_by'      => $this->staff->id,
        ]);

        $this->actingAs($this->gm)
            ->post(route('approval-center.trial-rms.reject', $trial), ['rejection_notes' => 'Tidak sesuai'])
            ->assertForbidden();

        $this->assertSame('Pending Tahap 1', $trial->fresh()->approval_status);
    }

    public function test_om_tetap_bisa_menolak_formula_tahap_om(): void
    {
        $formula = $this->makeFormula('Pending Tahap 1');

        $this->actingAs($this->manager)
            ->post(route('approval-center.formulas.reject', $formula), ['rejection_notes' => 'Perlu revisi'])
            ->assertRedirect(route('approval-center.index'));

        $this->assertSame('Rejected', $formula->fresh()->approval_status);
    }

    // K-07
    public function test_user_tanpa_jabatan_tidak_bisa_membuka_timeline(): void
    {
        $noRole = User::factory()->create();

        $this->actingAs($noRole)->get(route('timeline.index'))->assertForbidden();
        $this->actingAs($this->staff)->get(route('timeline.index'))->assertOk();
    }

    // S-10
    public function test_revisi_baru_formula_approval_dimulai_sebagai_draft(): void
    {
        $form = $this->makeApprovalForm('Approved');

        $this->actingAs($this->staff)
            ->post(route('formula-approvals.duplicate', $form))
            ->assertRedirect();

        $copy = FormulaApprovalForm::where('revision', 1)->firstOrFail();
        $this->assertSame('Draft', $copy->approval_status);
    }

    public function test_staff_lain_tidak_bisa_membuat_revisi_dokumen_orang_lain(): void
    {
        $form = $this->makeApprovalForm('Approved');

        $this->actingAs($this->otherStaff)
            ->post(route('formula-approvals.duplicate', $form))
            ->assertForbidden();

        $this->assertSame(1, FormulaApprovalForm::count());
    }

    // ── Tahap 3 ─────────────────────────────────────────────────────

    // K-05
    public function test_menonaktifkan_user_tidak_menghapus_datanya(): void
    {
        $admin = $this->makeSuperadmin();
        $formula = $this->makeFormula('Approved');

        $this->actingAs($admin)
            ->delete(route('users.destroy', $this->staff))
            ->assertRedirect(route('users.index'));

        $this->assertFalse($this->staff->fresh()->is_active);
        $this->assertNotNull($formula->fresh());
    }

    public function test_user_nonaktif_tidak_bisa_login(): void
    {
        $this->staff->update(['is_active' => false]);

        $this->post('/login', ['email' => $this->staff->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_yang_dinonaktifkan_saat_login_langsung_dikeluarkan(): void
    {
        $this->staff->update(['is_active' => false]);

        $this->actingAs($this->staff)
            ->get(route('timeline.index'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_superadmin_bisa_mengaktifkan_kembali_user(): void
    {
        $this->staff->update(['is_active' => false]);

        $this->actingAs($this->makeSuperadmin())
            ->patch(route('users.activate', $this->staff))
            ->assertRedirect(route('users.index'));

        $this->assertTrue($this->staff->fresh()->is_active);
    }

    public function test_database_menolak_hapus_user_yang_punya_data(): void
    {
        $this->makeFormula('Approved');

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->staff->delete();
    }

    // K-06
    public function test_bahan_baku_dan_supplier_yang_dipakai_formula_tidak_bisa_dihapus(): void
    {
        $material = Material::create(['name' => 'Bahan Dipakai', 'unit' => 'kg']);
        $supplier = \App\Models\Supplier::create(['name' => 'Supplier Dipakai']);
        $this->makeFormula('Approved')->materials()->create([
            'material_id' => $material->id,
            'supplier_id' => $supplier->id,
            'percentage'  => 100,
        ]);

        $this->actingAs($this->staff)
            ->delete(route('materials.destroy', $material))
            ->assertSessionHas('error');
        $this->actingAs($this->staff)
            ->delete(route('suppliers.destroy', $supplier))
            ->assertSessionHas('error');

        $this->assertNotNull($material->fresh());
        $this->assertNotNull($supplier->fresh());
        $this->assertSame(1, \App\Models\FormulaMaterial::count());
    }

    public function test_bahan_baku_yang_tidak_dipakai_tetap_bisa_dihapus(): void
    {
        $material = Material::create(['name' => 'Bahan Bebas', 'unit' => 'kg']);

        $this->actingAs($this->staff)
            ->delete(route('materials.destroy', $material))
            ->assertSessionHas('success');

        $this->assertNull($material->fresh());
    }

    public function test_produk_yang_terhubung_ke_modul_tidak_bisa_dihapus(): void
    {
        $product = Product::create(['name' => 'Produk Terpakai']);
        Formula::create([
            'code'            => 'FRM-' . uniqid(),
            'name'            => 'Formula Uji',
            'version'         => 1,
            'approval_status' => 'Draft',
            'product_id'      => $product->id,
            'created_by'      => $this->staff->id,
        ]);

        $this->actingAs($this->staff)
            ->delete(route('products.destroy', $product))
            ->assertSessionHas('error');

        $this->assertNotNull($product->fresh());
    }

    // ── Tahap 4 ─────────────────────────────────────────────────────

    // S-01
    public function test_trial_rm_yang_ditolak_bisa_direvisi(): void
    {
        $trial = TrialRm::create([
            'code'            => 'TRM-202610-002-A',
            'formula_id'      => $this->makeFormula('Approved')->id,
            'sample_identity' => 'Sampel Lama',
            'process_steps'   => 'Langkah lama',
            'approval_status' => 'Rejected',
            'created_by'      => $this->staff->id,
        ]);

        $this->actingAs($this->staff)
            ->put(route('trial-rms.update', $trial), [
                'code'            => 'TRM-202610-002-A',
                'sample_identity' => 'Sampel Revisi',
                'process_steps'   => 'Langkah revisi',
            ])
            ->assertRedirect(route('trial-rms.show', $trial));

        $this->assertSame('Sampel Revisi', $trial->fresh()->sample_identity);
    }

    // S-03
    public function test_trial_pm_yang_ditolak_bisa_diajukan_ulang_dan_persetujuan_direset(): void
    {
        $trial = app(\App\Services\TrialPmService::class)->create([
            'packaging_material'    => 'Botol PET',
            'supplier'              => 'PT Kemas',
            'product_use'           => ['Produk'],
            'product_trial'         => ['Batch A'],
            'trial_sample_quantity' => '100 pcs',
            'specifications'        => ['Spesifikasi'],
        ], $this->staff->id);
        $trial->departmentApprovals()->where('department', 'rd')->update(['is_approved' => true, 'approved_by' => $this->otherStaff->id]);
        $trial->update(['approval_status' => 'Rejected', 'rejection_notes' => 'Ditolak QC']);

        $this->actingAs($this->staff)->get(route('trial-pms.edit', $trial))->assertOk();
        $this->actingAs($this->staff)
            ->post(route('trial-pms.submit', $trial))
            ->assertRedirect(route('trial-pms.show', $trial));

        $this->assertSame('Pending Review', $trial->fresh()->approval_status);
        $this->assertSame(0, $trial->departmentApprovals()->where('is_approved', true)->count());
    }

    // S-04
    public function test_filter_status_log_book_memakai_status_baru(): void
    {
        LogbookPm::create([
            'tanggal_terima'       => '2026-10-01',
            'jenis_kemasan'        => 'Botol',
            'nama_material'        => 'Botol Lulus Uji',
            'jumlah_diterima'      => 10,
            'satuan'               => 'pcs',
            'kelengkapan_dokumen'  => 'Lengkap',
            'kondisi_fisik_aktual' => 'Baik',
            'status_pengujian'     => 'Passed',
            'nama_penerima'        => 'Penerima',
            'created_by'           => $this->staff->id,
        ]);

        $this->actingAs($this->staff)
            ->get(route('logbook-pm.index', ['status_pengujian' => 'Passed']))
            ->assertOk()
            ->assertSee('value="On Going Trial"', false)
            ->assertSee('Botol Lulus Uji');
    }

    // S-05
    public function test_antrean_gm_memuat_dokumen_approval_formula_design(): void
    {
        $form = $this->makeApprovalForm('Pending');
        $form->update(['source' => 'approval-formula-design']);

        $this->actingAs($this->gm)
            ->get(route('approval-center.index'))
            ->assertOk()
            ->assertViewHas('pendingFormulaApprovals', fn ($forms) => $forms->contains('id', $form->id))
            ->assertSee(route('approval-formula-designs.approve-gm', $form), false);
    }

    // S-08
    public function test_mengganti_paraf_tidak_menghapus_file_paraf_lama(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $admin = $this->makeSuperadmin();
        $payload = ['app_name' => 'RnD', 'company_name' => 'Herbatech'];

        $this->actingAs($admin)->put(route('settings.update'), $payload + [
            'paraf_qc' => \Illuminate\Http\UploadedFile::fake()->image('paraf-lama.png'),
        ]);
        $oldPath = setting('paraf_qc');

        $this->actingAs($admin)->put(route('settings.update'), $payload + [
            'paraf_qc' => \Illuminate\Http\UploadedFile::fake()->image('paraf-baru.png'),
        ]);

        $this->assertNotSame($oldPath, setting('paraf_qc'));
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($oldPath);
    }

    // S-09
    public function test_reformulasi_menyalin_produk_dan_nomor_versi_tidak_ganda(): void
    {
        $product = Product::create(['name' => 'Produk Reformulasi']);
        $v1 = $this->makeFormula('Approved');
        $v1->update(['product_id' => $product->id, 'target_sachet' => 20]);
        $v2 = $v1->replicate()->fill(['code' => 'FRM-V2', 'version' => 2, 'parent_formula_id' => $v1->id]);
        $v2->save();
        $v3 = $v1->replicate()->fill(['code' => 'FRM-V3', 'version' => 3, 'parent_formula_id' => $v1->id]);
        $v3->save();

        // Reformulasi dari V2 saat V3 sudah ada → harus menjadi V4
        $this->actingAs($this->staff)->post(route('formulas.reformulate', $v2))->assertRedirect();

        $newest = Formula::latest('id')->firstOrFail();
        $this->assertSame(4, (int) $newest->version);
        $this->assertSame($v1->id, $newest->parent_formula_id);
        $this->assertSame($product->id, $newest->product_id);
        $this->assertSame(20, (int) $newest->target_sachet);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    private function makeSuperadmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('Superadmin');

        return $admin;
    }

    private function makeFormula(string $status): Formula
    {
        return Formula::create([
            'code'            => 'FRM-' . uniqid(),
            'name'            => 'Formula Uji',
            'version'         => 1,
            'approval_status' => $status,
            'created_by'      => $this->staff->id,
        ]);
    }

    private function makeApprovalForm(string $status): FormulaApprovalForm
    {
        return FormulaApprovalForm::create([
            'type'            => 'Formula',
            'source'          => 'formula-approval',
            'product_name'    => 'Produk Uji',
            'kategori'        => 'New Product',
            'revision'        => 0,
            'approval_status' => $status,
            'created_by'      => $this->staff->id,
        ]);
    }
}
