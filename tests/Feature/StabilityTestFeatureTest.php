<?php

namespace Tests\Feature;

use App\Models\StabilityTest;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->staff = User::factory()->create();
        $this->staff->assignRole('Staff R&D');

        $this->manager = User::factory()->create();
        $this->manager->assignRole('Operational Manager');
    }

    public function test_staff_dapat_membuat_stability_test_dengan_lampiran()
    {
        Storage::fake('public');

        $this->actingAs($this->staff)
            ->post(route('stability-tests.store'), [
                'title' => 'Uji Stabilitas Jahe Merah Hangat',
                'files' => [
                    UploadedFile::fake()->create('protokol.pdf', 200, 'application/pdf'),
                    UploadedFile::fake()->create('laporan.docx', 150, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('stability_tests', [
            'title'      => 'Uji Stabilitas Jahe Merah Hangat',
            'created_by' => $this->staff->id,
        ]);

        $test = StabilityTest::where('title', 'Uji Stabilitas Jahe Merah Hangat')->first();

        $this->assertDatabaseHas('stability_test_attachments', [
            'stability_test_id' => $test->id,
            'original_name'     => 'protokol.pdf',
        ]);
        $this->assertDatabaseHas('stability_test_attachments', [
            'stability_test_id' => $test->id,
            'original_name'     => 'laporan.docx',
        ]);
    }

    public function test_judul_tes_wajib_diisi()
    {
        $this->actingAs($this->staff)
            ->post(route('stability-tests.store'), [])
            ->assertSessionHasErrors('title');
    }

    public function test_manager_tidak_bisa_membuat_stability_test()
    {
        $this->actingAs($this->manager)
            ->post(route('stability-tests.store'), [
                'title' => 'Uji Stabilitas Rahasia',
            ])
            ->assertForbidden();
    }

    public function test_index_menampilkan_dan_staff_dapat_hapus_lampiran()
    {
        Storage::fake('public');

        $test = StabilityTest::create([
            'title'      => 'Uji Stabilitas Kunyit',
            'created_by' => $this->staff->id,
        ]);

        $attachment = $test->attachments()->create([
            'file_path'     => 'stability-tests/example.pdf',
            'original_name' => 'example.pdf',
            'uploaded_by'   => $this->staff->id,
        ]);

        $this->actingAs($this->staff)
            ->get(route('stability-tests.index'))
            ->assertOk()
            ->assertSee('Uji Stabilitas Kunyit');

        $this->actingAs($this->staff)
            ->delete(route('stability-tests.attachments.destroy', [$test, $attachment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('stability_test_attachments', ['id' => $attachment->id]);
    }
}