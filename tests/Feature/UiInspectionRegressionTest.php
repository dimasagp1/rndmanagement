<?php

namespace Tests\Feature;

use App\Models\Formula;
use App\Models\FormulaApprovalForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi untuk temuan inspeksi UI/UX (lihat LAPORAN_INSPEKSI_UIUX.md).
 */
class UiInspectionRegressionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->staff = User::factory()->create();
        $this->staff->assignRole('Staff R&D');
    }

    // UI-01
    public function test_empty_state_tidak_menampilkan_entity_html_ganda(): void
    {
        $this->actingAs($this->staff)
            ->get(route('approval-formula-designs.index', ['type' => 'Design']))
            ->assertOk()
            ->assertSee('Final approval formula & artwork')
            ->assertDontSee('&amp;amp;', false);
    }

    // UI-02
    public function test_badge_role_staff_packdev_benar(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Superadmin');
        $packdev = User::factory()->create(['name' => 'Petugas Packdev']);
        $packdev->assignRole('Staff Packdev');

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Staff Packdev');
    }

    // UI-03
    public function test_filter_pending_dashboard_mencakup_semua_tahap_pending(): void
    {
        Formula::create([
            'code' => 'FRM-UI-1', 'name' => 'Formula T1', 'version' => 1,
            'approval_status' => 'Pending Tahap 1', 'created_by' => $this->staff->id,
        ]);
        Formula::create([
            'code' => 'FRM-UI-2', 'name' => 'Formula Draft', 'version' => 1,
            'approval_status' => 'Draft', 'created_by' => $this->staff->id,
        ]);
        FormulaApprovalForm::create(['product_name' => 'Form Pending', 'approval_status' => 'Pending']);

        $this->actingAs($this->staff)
            ->get(route('timeline.index', ['status' => 'Pending']))
            ->assertOk()
            ->assertViewHas('totalItems', 2)
            ->assertViewHas('pending', 2);
    }

    // UI-04
    public function test_menu_sidebar_memuat_preformulation_study(): void
    {
        $this->actingAs($this->staff)
            ->get(route('preformulation-studies.index'))
            ->assertOk()
            ->assertSee('href="' . route('preformulation-studies.index') . '"', false)
            ->assertSee('Preformulation Study');
    }

    // UI-05
    public function test_tombol_setujui_di_approval_center_meminta_konfirmasi(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Operational Manager');
        Formula::create([
            'code' => 'FRM-UI-3', 'name' => 'Formula Antre', 'version' => 1,
            'approval_status' => 'Pending Tahap 1', 'created_by' => $this->staff->id,
        ]);

        $this->actingAs($manager)
            ->get(route('approval-center.index'))
            ->assertOk()
            ->assertSee("return confirm('Setujui Formula FRM-UI-3?", false);
    }
}
