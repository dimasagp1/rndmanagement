<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\MaterialSupplier;
use Spatie\Permission\Models\Role;

class MaterialSupplierTrialStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'Superadmin']);
        $this->user = User::factory()->create();
        $this->user->assignRole($role);
    }

    public function test_can_create_trial_material_and_promote_to_commercial(): void
    {
        // 1. Create trial material
        $response = $this->actingAs($this->user)->post(route('materials.store'), [
            'name'        => 'Ekstrak Bunga Telang Lab Trial',
            'code'        => 'RM-TR-001',
            'category_id' => 1,
            'unit'        => 'kg',
            'status'      => 'trial',
        ]);

        $response->assertRedirect(route('materials.index'));

        $material = Material::where('name', 'Ekstrak Bunga Telang Lab Trial')->first();
        $this->assertNotNull($material);
        $this->assertEquals('trial', $material->status);
        $this->assertTrue($material->isTrial());

        // 2. Promote to commercial
        $promoteResponse = $this->actingAs($this->user)->post(route('materials.promote', $material));
        $promoteResponse->assertRedirect();

        $material->refresh();
        $this->assertEquals('commercial', $material->status);
        $this->assertTrue($material->isCommercial());
    }

    public function test_can_create_trial_supplier_and_promote_to_commercial(): void
    {
        // 1. Create trial supplier
        $response = $this->actingAs($this->user)->post(route('suppliers.store'), [
            'name'   => 'PT Herbal Sample Mandiri',
            'status' => 'trial',
        ]);

        $response->assertRedirect(route('suppliers.index'));

        $supplier = Supplier::where('name', 'PT Herbal Sample Mandiri')->first();
        $this->assertNotNull($supplier);
        $this->assertEquals('trial', $supplier->status);
        $this->assertTrue($supplier->isTrial());

        // 2. Promote to commercial (AVL)
        $promoteResponse = $this->actingAs($this->user)->post(route('suppliers.promote', $supplier));
        $promoteResponse->assertRedirect();

        $supplier->refresh();
        $this->assertEquals('commercial', $supplier->status);
        $this->assertTrue($supplier->isCommercial());
    }

    public function test_price_api_returns_trial_status(): void
    {
        $material = Material::create([
            'name'   => 'Bahan Baku Uji Coba',
            'unit'   => 'kg',
            'status' => 'trial',
        ]);
        $supplier = Supplier::create([
            'name'   => 'Supplier Sample',
            'status' => 'trial',
        ]);

        $rel = MaterialSupplier::create([
            'material_id'      => $material->id,
            'supplier_id'      => $supplier->id,
            'current_price'    => 175000,
            'currency'         => 'IDR',
            'status'           => 'trial',
            'is_active'        => true,
            'effective_date'   => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/material-suppliers/price?material_id={$material->id}&supplier_id={$supplier->id}");

        $response->assertStatus(200)
            ->assertJson([
                'found'        => true,
                'price_per_kg' => 175000,
                'supplier_id'  => $supplier->id,
                'status'       => 'trial',
                'is_trial'     => true,
            ]);

        // Promote price relationship
        $this->actingAs($this->user)->post(route('material-suppliers.promote', $rel));
        $rel->refresh();
        $this->assertEquals('commercial', $rel->status);
    }
}
