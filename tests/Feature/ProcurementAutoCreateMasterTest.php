<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\MaterialSupplier;
use App\Models\ProcurementVendorPrice;
use App\Models\ProcurementMappingAlias;
use Spatie\Permission\Models\Role;

class ProcurementAutoCreateMasterTest extends TestCase
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

    public function test_can_auto_create_new_master_material_and_supplier_from_unmapped_feed(): void
    {
        $feed = ProcurementVendorPrice::create([
            'po_number'      => 'PO/2026/10/001',
            'material_name'  => 'Daun Stevia Rebaudiana Bubuk',
            'vendor_name'    => 'PT Pemanis Alami Sejahtera',
            'price_per_unit' => 310000,
            'unit'           => 'kg',
            'currency'       => 'IDR',
            'po_date'        => '2026-10-15',
            'sync_status'    => 'unmapped',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('procurement-prices.create-master', $feed));

        $response->assertRedirect();

        // 1. Master material created
        $material = Material::where('name', 'Daun Stevia Rebaudiana Bubuk')->first();
        $this->assertNotNull($material);
        $this->assertEquals('commercial', $material->status);

        // 2. Master supplier created
        $supplier = Supplier::where('name', 'PT Pemanis Alami Sejahtera')->first();
        $this->assertNotNull($supplier);
        $this->assertEquals('commercial', $supplier->status);

        // 3. Mapping aliases stored for future Odoo pulls
        $matAlias = ProcurementMappingAlias::resolveMaterial('Daun Stevia Rebaudiana Bubuk');
        $this->assertNotNull($matAlias);
        $this->assertEquals($material->id, $matAlias->id);

        $supAlias = ProcurementMappingAlias::resolveSupplier('PT Pemanis Alami Sejahtera');
        $this->assertNotNull($supAlias);
        $this->assertEquals($supplier->id, $supAlias->id);

        // 4. Feed item updated to applied
        $feed->refresh();
        $this->assertEquals('applied', $feed->sync_status);
        $this->assertEquals($material->id, $feed->matched_material_id);
        $this->assertEquals($supplier->id, $feed->matched_supplier_id);

        // 5. MaterialSupplier active price created
        $matSup = MaterialSupplier::where('material_id', $material->id)
            ->where('supplier_id', $supplier->id)
            ->first();
        $this->assertNotNull($matSup);
        $this->assertEquals(310000, $matSup->current_price);
        $this->assertEquals('commercial', $matSup->status);
    }

    public function test_can_bulk_create_all_unmapped_masters(): void
    {
        ProcurementVendorPrice::create([
            'po_number'      => 'PO/2026/10/010',
            'material_name'  => 'Minyak Atsiri Rosemary',
            'vendor_name'    => 'CV Aroma Flavour Nusantara',
            'price_per_unit' => 450000,
            'unit'           => 'kg',
            'currency'       => 'IDR',
            'sync_status'    => 'unmapped',
        ]);

        ProcurementVendorPrice::create([
            'po_number'      => 'PO/2026/10/011',
            'material_name'  => 'Propolis Trigona Cair',
            'vendor_name'    => 'PT Lebah Berkah Mandiri',
            'price_per_unit' => 850000,
            'unit'           => 'kg',
            'currency'       => 'IDR',
            'sync_status'    => 'unmapped',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('procurement-prices.create-all-masters'));

        $response->assertRedirect();

        $this->assertDatabaseHas('materials', ['name' => 'Minyak Atsiri Rosemary', 'status' => 'commercial']);
        $this->assertDatabaseHas('materials', ['name' => 'Propolis Trigona Cair', 'status' => 'commercial']);
        $this->assertDatabaseHas('suppliers', ['name' => 'CV Aroma Flavour Nusantara', 'status' => 'commercial']);
        $this->assertDatabaseHas('suppliers', ['name' => 'PT Lebah Berkah Mandiri', 'status' => 'commercial']);

        $unmappedRemaining = ProcurementVendorPrice::where('sync_status', 'unmapped')->count();
        $this->assertEquals(0, $unmappedRemaining);
    }

    public function test_mapping_with_promote_promotes_trial_material_and_supplier_to_commercial(): void
    {
        $trialMaterial = Material::create([
            'name'   => 'Bahan Riset Lab Alfa',
            'unit'   => 'kg',
            'status' => 'trial',
        ]);

        $trialSupplier = Supplier::create([
            'name'   => 'Vendor Sample Beta',
            'status' => 'trial',
        ]);

        $feed = ProcurementVendorPrice::create([
            'po_number'      => 'PO/2026/10/099',
            'material_name'  => 'Raw Alfa Lab Grade',
            'vendor_name'    => 'PT Beta Sample Indonesia',
            'price_per_unit' => 120000,
            'unit'           => 'kg',
            'currency'       => 'IDR',
            'sync_status'    => 'unmapped',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('procurement-prices.map', $feed), [
                'material_id'           => $trialMaterial->id,
                'supplier_id'           => $trialSupplier->id,
                'propagate'             => true,
                'promote_to_commercial' => true,
            ]);

        $response->assertRedirect();

        $trialMaterial->refresh();
        $trialSupplier->refresh();

        $this->assertEquals('commercial', $trialMaterial->status);
        $this->assertEquals('commercial', $trialSupplier->status);
    }
}
