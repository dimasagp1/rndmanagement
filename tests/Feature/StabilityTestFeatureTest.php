<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StabilityTest;
use App\Models\StabilityTestIssue;
use App\Models\StabilityTestSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StabilityTestFeatureTest extends TestCase
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
        Product::create(['name' => 'Produk Lain']);
    }

    private function createTest(array $overrides = []): StabilityTest
    {
        return StabilityTest::create([
            'product_id'        => $this->product->id,
            'product_name'      => 'Jahe Merah Hangat',
            'batch_number'      => 'HBI-2608-01',
            'stability_protocol'=> 'Uji stabilitas long term.',
            'storage_condition' => 'Long Term (25°C/60%RH)',
            'approval_status'   => 'Draft',
            'created_by'        => $this->staff->id,
            ...$overrides,
        ]);
    }

    public function test_staff_dapat_membuka_halaman_create()
    {
        $this->actingAs($this->staff)
            ->get(route('stability-tests.create'))
            ->assertOk()
            ->assertSee('Jahe Merah Hangat');
    }

    public function test_staff_dapat_membuat_stability_test()
    {
        $this->actingAs($this->staff)
            ->post(route('stability-tests.store'), [
                'product_id'         => $this->product->id,
                'batch_number'       => 'HBI-2608-01',
                'stability_protocol' => 'Uji stabilitas long term.',
                'storage_condition'  => 'Long Term (25°C/60%RH)',
            ])
            ->assertRedirect(route('stability-tests.index'));

        $this->assertDatabaseHas('stability_tests', [
            'product_id'        => $this->product->id,
            'product_name'      => 'Jahe Merah Hangat',
            'batch_number'      => 'HBI-2608-01',
            'approval_status'   => 'Draft',
            'created_by'        => $this->staff->id,
        ]);
    }

    public function test_manager_tidak_bisa_membuat_stability_test()
    {
        $this->actingAs($this->manager)
            ->post(route('stability-tests.store'), [
                'product_id'   => $this->product->id,
                'batch_number' => 'HBI-2608-01',
            ])
            ->assertForbidden();
    }

    public function test_produk_yang_sudah_punya_stability_test_tidak_bisa_dipakai_lagi()
    {
        $this->createTest();

        $this->actingAs($this->staff)
            ->post(route('stability-tests.store'), [
                'product_id'   => $this->product->id,
                'batch_number' => 'HBI-2608-02',
            ])
            ->assertSessionHasErrors('product_id');
    }

    public function test_alur_approval_protokol_oleh_om_lalu_laporan_oleh_gm()
    {
        $test = $this->createTest();

        // GM tidak bisa approve sebelum OM menyetujui protokol
        $this->actingAs($this->gm)
            ->post(route('stability-tests.approve-report', $test))
            ->assertStatus(422);

        // Staff ajukan protokol
        $this->actingAs($this->staff)
            ->post(route('stability-tests.submit-protocol', $test))
            ->assertRedirect(route('stability-tests.show', $test));
        $test->refresh();
        $this->assertEquals('Pending Protokol', $test->approval_status);
        $this->assertNotNull($test->submitted_at);

        // OM setujui protokol
        $this->actingAs($this->manager)
            ->post(route('stability-tests.approve-protocol', $test))
            ->assertRedirect(route('stability-tests.show', $test));
        $test->refresh();
        $this->assertEquals('Protokol Approved', $test->approval_status);
        $this->assertEquals($this->manager->id, $test->approved_by_om);

        // Laporan belum bisa diajukan tanpa jadwal Completed
        $this->actingAs($this->staff)
            ->post(route('stability-tests.submit-report', $test), [
                'stability_conclusion' => 'Produk stabil.',
            ])
            ->assertSessionHasErrors('schedules');

        // Tambah jadwal + parameter hasil uji
        $this->actingAs($this->staff)
            ->post(route('stability-tests.schedules.store', $test), [
                'timepoint' => 'Bulan 0',
                'due_date'  => now()->subMonth()->format('Y-m-d'),
            ]);
        $schedule = $test->schedules()->first();

        $this->actingAs($this->staff)
            ->post(route('stability-tests.parameters.store', [$test, $schedule]), [
                'parameter'     => 'Kadar Air',
                'specification' => '≤ 5%',
                'unit'          => '%',
                'result'        => '3.2',
                'result_status' => 'Sesuai',
            ]);

        $parameter = $schedule->parameters()->first();
        $this->actingAs($this->staff)
            ->put(route('stability-tests.parameters.update', [$test, $schedule, $parameter]), [
                'result'        => '3.2',
                'result_status' => 'Sesuai',
            ]);
        $schedule->refresh();
        $this->assertEquals('Completed', $schedule->status);
        $this->assertNotNull($schedule->tested_at);

        // Staff ajukan laporan
        $this->actingAs($this->staff)
            ->post(route('stability-tests.submit-report', $test), [
                'stability_conclusion' => 'Produk stabil pada seluruh titik uji.',
            ])
            ->assertRedirect(route('stability-tests.show', $test));
        $test->refresh();
        $this->assertEquals('Pending Laporan', $test->approval_status);

        // GM setujui laporan
        $this->actingAs($this->gm)
            ->post(route('stability-tests.approve-report', $test))
            ->assertRedirect(route('stability-tests.show', $test));
        $test->refresh();
        $this->assertEquals('Approved', $test->approval_status);
        $this->assertEquals($this->gm->id, $test->approved_by_gm);
        $this->assertNotNull($test->approved_at_gm);
    }

    public function test_staff_tidak_bisa_approve_protokol()
    {
        $test = $this->createTest(['approval_status' => 'Pending Protokol']);

        $this->actingAs($this->staff)
            ->post(route('stability-tests.approve-protocol', $test))
            ->assertForbidden();
    }

    public function test_om_bisa_tolak_protokol_dengan_catatan()
    {
        $test = $this->createTest(['approval_status' => 'Pending Protokol']);

        $this->actingAs($this->manager)
            ->post(route('stability-tests.reject', $test), [
                'rejection_notes' => 'Protokol perlu dilengkapi titik uji bulan 12.',
            ])
            ->assertRedirect(route('stability-tests.show', $test));

        $test->refresh();
        $this->assertEquals('Rejected', $test->approval_status);
        $this->assertEquals('Protokol perlu dilengkapi titik uji bulan 12.', $test->rejection_notes);
    }

    public function test_gm_bisa_tolak_laporan()
    {
        $test = $this->createTest([
            'approval_status'     => 'Pending Laporan',
            'submitted_at'        => now(),
            'report_submitted_at' => now(),
        ]);

        $this->actingAs($this->gm)
            ->post(route('stability-tests.reject', $test), [
                'rejection_notes' => 'Kesimpulan tidak didukung data hasil uji.',
            ])
            ->assertRedirect(route('stability-tests.show', $test));

        $test->refresh();
        $this->assertEquals('Rejected', $test->approval_status);
    }

    public function test_staff_dapat_mencatat_dan_menutup_issue_oos()
    {
        $test = $this->createTest(['approval_status' => 'Protokol Approved']);

        $this->actingAs($this->staff)
            ->post(route('stability-tests.issues.store', $test), [
                'issue_type'  => 'OOS',
                'description' => 'Kadar air melebihi spesifikasi pada bulan 3.',
            ])
            ->assertRedirect();

        $issue = $test->issues()->first();
        $this->assertNotNull($issue);
        $this->assertEquals('Open', $issue->status);

        $this->actingAs($this->staff)
            ->put(route('stability-tests.issues.update', [$test, $issue]), [
                'status'     => 'Closed',
                'resolution' => 'Ulang pengujian dengan alat baru, hasil normal.',
            ]);

        $issue->refresh();
        $this->assertEquals('Closed', $issue->status);
        $this->assertEquals('Ulang pengujian dengan alat baru, hasil normal.', $issue->resolution);
    }

    public function test_staff_dapat_upload_attachment_pdf_dan_word()
    {
        Storage::fake('public');

        $test = $this->createTest();

        $this->actingAs($this->staff)
            ->post(route('stability-tests.attachments.store', $test), [
                'type' => 'Protokol Stabilitas',
                'file' => UploadedFile::fake()->create('protokol.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('stability_test_attachments', [
            'stability_test_id' => $test->id,
            'type'              => 'Protokol Stabilitas',
            'original_name'     => 'protokol.pdf',
        ]);

        $this->actingAs($this->staff)
            ->post(route('stability-tests.attachments.store', $test), [
                'type' => 'Laporan Hasil Stabilitas',
                'file' => UploadedFile::fake()->create('laporan.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            ])
            ->assertRedirect();

        $this->assertCount(2, $test->attachments()->get());
    }

    public function test_upload_file_non_pdf_word_ditolak()
    {
        $test = $this->createTest();

        $this->actingAs($this->staff)
            ->post(route('stability-tests.attachments.store', $test), [
                'type' => 'Lainnya',
                'file' => UploadedFile::fake()->create('gambar.png', 100, 'image/png'),
            ])
            ->assertSessionHasErrors('file');
    }

    public function test_approval_center_menampilkan_antrean_stability_test_per_role()
    {
        $this->createTest(['approval_status' => 'Pending Protokol']);
        $this->createTest([
            'product_id'        => Product::where('name', 'Produk Lain')->first()->id,
            'product_name'      => 'Produk Lain',
            'batch_number'      => 'HBI-2608-02',
            'approval_status'   => 'Pending Laporan',
            'submitted_at'      => now(),
            'report_submitted_at' => now(),
        ]);

        // OM hanya melihat antrean protokol
        $this->actingAs($this->manager)
            ->get(route('approval-center.index'))
            ->assertOk()
            ->assertSee('Jahe Merah Hangat')
            ->assertDontSee('Produk Lain');

        // GM hanya melihat antrean laporan
        $this->actingAs($this->gm)
            ->get(route('approval-center.index'))
            ->assertOk()
            ->assertSee('Produk Lain')
            ->assertDontSee('Jahe Merah Hangat');
    }
}