<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Material;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Roles and Permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // Create Users
        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole('Superadmin');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('Staff R&D');
    }

    // ──────────────────────────────────────────────────────────────
    // SYSTEM SETTINGS TESTS
    // ──────────────────────────────────────────────────────────────
    public function test_superadmin_can_access_settings_page()
    {
        $response = $this->actingAs($this->superadmin)->get(route('settings.index'));
        $response->assertStatus(200);
    }

    public function test_staff_cannot_access_settings_page()
    {
        $response = $this->actingAs($this->staff)->get(route('settings.index'));
        $response->assertStatus(403);
    }

    public function test_superadmin_can_update_brand_settings()
    {
        $response = $this->actingAs($this->superadmin)->put(route('settings.update'), [
            'app_name' => 'Custom App Name',
            'company_name' => 'Custom Company Name',
        ]);

        $response->assertStatus(302);
        $this->assertEquals('Custom App Name', setting('app_name'));
        $this->assertEquals('Custom Company Name', setting('company_name'));
    }

    // ──────────────────────────────────────────────────────────────
    // DATABASE DATA MASTER TESTS
    // ──────────────────────────────────────────────────────────────
    public function test_superadmin_can_crud_materials()
    {
        // Store
        $response = $this->actingAs($this->superadmin)->post(route('materials.store'), [
            'name' => 'Material Test Spec',
            'type' => 'Ekstrak',
            'unit' => 'kg',
        ]);
        $response->assertRedirect(route('materials.index'));
        $this->assertDatabaseHas('materials', ['name' => 'Material Test Spec', 'type' => 'Ekstrak']);

        $material = Material::where('name', 'Material Test Spec')->first();

        // Update
        $response = $this->actingAs($this->superadmin)->put(route('materials.update', $material), [
            'name' => 'Material Test Spec Updated',
            'type' => 'Madu',
            'unit' => 'liter',
        ]);
        $response->assertRedirect(route('materials.index'));
        $this->assertDatabaseHas('materials', ['name' => 'Material Test Spec Updated', 'type' => 'Madu']);

        // Destroy
        $response = $this->actingAs($this->superadmin)->delete(route('materials.destroy', $material));
        $response->assertRedirect(route('materials.index'));
        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
    }

    public function test_staff_can_crud_materials()
    {
        $material = Material::create(['name' => 'M1', 'type' => 'Ekstrak', 'unit' => 'kg']);

        $this->actingAs($this->staff)->get(route('materials.index'))->assertStatus(200);
        $this->actingAs($this->staff)->post(route('materials.store'), [
            'name' => 'Staff Material Test',
            'type' => 'Bubuk',
            'unit' => 'gram',
        ])->assertRedirect(route('materials.index'));
        
        $this->actingAs($this->staff)->put(route('materials.update', $material), [
            'name' => 'M1 Updated By Staff',
            'type' => 'Ekstrak',
            'unit' => 'kg',
        ])->assertRedirect(route('materials.index'));

        $this->actingAs($this->staff)->delete(route('materials.destroy', $material))->assertRedirect(route('materials.index'));
    }

    public function test_superadmin_can_crud_suppliers()
    {
        // Store
        $response = $this->actingAs($this->superadmin)->post(route('suppliers.store'), [
            'name' => 'Supplier Test Inc',
            'contact' => 'John Doe',
        ]);
        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['name' => 'Supplier Test Inc', 'contact' => 'John Doe']);

        $supplier = Supplier::where('name', 'Supplier Test Inc')->first();

        // Update
        $response = $this->actingAs($this->superadmin)->put(route('suppliers.update', $supplier), [
            'name' => 'Supplier Test Inc Updated',
            'contact' => 'Jane Doe',
        ]);
        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['name' => 'Supplier Test Inc Updated', 'contact' => 'Jane Doe']);

        // Destroy
        $response = $this->actingAs($this->superadmin)->delete(route('suppliers.destroy', $supplier));
        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}
