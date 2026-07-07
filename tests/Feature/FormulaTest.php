<?php

namespace Tests\Feature;

use App\Models\Formula;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\User;
use App\Services\FormulaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FormulaTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $manager;
    private Material $material1;
    private Material $material2;
    private Supplier $supplier;

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

        // Create Materials & Supplier
        $this->material1 = Material::create([
            'name' => 'Ekstrak Jahe',
            'type' => 'Ekstrak',
            'unit' => 'kg',
        ]);
        $this->material2 = Material::create([
            'name' => 'Madu Murni',
            'type' => 'Cairan',
            'unit' => 'liter',
        ]);
        $this->supplier = Supplier::create([
            'name' => 'PT Alam Herbal',
            'contact' => 'Budi',
            'phone' => '0812',
            'email' => 'budi@alam.com',
            'address' => 'Jakarta',
        ]);
    }

    public function test_staff_rnd_can_create_formula_draft_with_any_percentage()
    {
        $response = $this->actingAs($this->staff)->post(route('formulas.store'), [
            'code' => 'FRM-202607-001',
            'name' => 'Formula Test',
            'development_stage' => 'Product Form',
            'materials' => [
                [
                    'material_id' => $this->material1->id,
                    'supplier_id' => $this->supplier->id,
                    'percentage' => 45.50,
                ],
            ],
        ]);

        $formula = Formula::first();
        $this->assertNotNull($formula);
        $this->assertEquals('Formula Test', $formula->name);
        $this->assertEquals(1, $formula->version);
        $this->assertEquals('Draft', $formula->approval_status);
        $this->assertEquals(45.50, $formula->total_percentage);
        $this->assertEquals('FRM-202607-001', $formula->code);

        $response->assertRedirect(route('formulas.show', $formula));
    }

    public function test_cannot_create_formula_with_percentage_exceeding_100()
    {
        $response = $this->actingAs($this->staff)->post(route('formulas.store'), [
            'code' => 'FRM-202607-999',
            'name' => 'Formula Over 100',
            'development_stage' => 'Product Form',
            'materials' => [
                [
                    'material_id' => $this->material1->id,
                    'supplier_id' => $this->supplier->id,
                    'percentage' => 110.00,
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['materials.0.percentage']);
        $this->assertEquals(0, Formula::count());
    }

    public function test_staff_rnd_cannot_submit_formula_with_total_percentage_not_100()
    {
        $formula = Formula::create([
            'code' => 'FRM-202607-001',
            'name' => 'Formula Belum 100',
            'version' => 1,
            'development_stage' => 'Draf',
            'approval_status' => 'Draft',
            'created_by' => $this->staff->id,
        ]);

        $formula->materials()->create([
            'material_id' => $this->material1->id,
            'supplier_id' => $this->supplier->id,
            'percentage' => 50.00,
        ]);

        $response = $this->actingAs($this->staff)->post(route('formulas.submit', $formula));
        $response->assertSessionHasErrors(['composition']);
        $this->assertEquals('Draft', $formula->fresh()->approval_status);
    }

    public function test_staff_rnd_can_submit_formula_with_exactly_100_percent()
    {
        $formula = Formula::create([
            'code' => 'FRM-202607-001',
            'name' => 'Formula Tepat 100',
            'version' => 1,
            'development_stage' => 'Draf',
            'approval_status' => 'Draft',
            'created_by' => $this->staff->id,
        ]);

        $formula->materials()->create([
            'material_id' => $this->material1->id,
            'supplier_id' => $this->supplier->id,
            'percentage' => 40.00,
        ]);

        $formula->materials()->create([
            'material_id' => $this->material2->id,
            'supplier_id' => $this->supplier->id,
            'percentage' => 60.00,
        ]);

        $response = $this->actingAs($this->staff)->post(route('formulas.submit', $formula));
        $response->assertRedirect(route('formulas.show', $formula));
        $this->assertEquals('Pending Tahap 1', $formula->fresh()->approval_status);
    }

    public function test_manager_cannot_edit_draft_formula()
    {
        $formula = Formula::create([
            'code' => 'FRM-202607-001',
            'name' => 'Formula Draft',
            'version' => 1,
            'development_stage' => 'Draf',
            'approval_status' => 'Draft',
            'created_by' => $this->staff->id,
        ]);

        // Manager should get 403 Forbidden
        $response = $this->actingAs($this->manager)->get(route('formulas.edit', $formula));
        $response->assertStatus(403);
    }

    public function test_approved_formula_can_be_reformulated_generating_new_version()
    {
        $formula = Formula::create([
            'code' => 'FRM-202607-001',
            'name' => 'Formula Approved',
            'version' => 1,
            'development_stage' => 'Final',
            'approval_status' => 'Approved',
            'created_by' => $this->staff->id,
        ]);

        $formula->materials()->create([
            'material_id' => $this->material1->id,
            'supplier_id' => $this->supplier->id,
            'percentage' => 100.00,
        ]);

        $response = $this->actingAs($this->staff)->post(route('formulas.reformulate', $formula));

        $newFormula = Formula::where('version', 2)->first();
        $this->assertNotNull($newFormula);
        $this->assertEquals('Formula Approved', $newFormula->name);
        $this->assertEquals(2, $newFormula->version);
        $this->assertEquals('Draft', $newFormula->approval_status);
        $this->assertEquals($formula->id, $newFormula->parent_formula_id);
        $this->assertEquals(100.00, $newFormula->total_percentage);

        $response->assertRedirect(route('formulas.edit', $newFormula));
    }
}
