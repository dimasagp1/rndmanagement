<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\MaterialSupplier;
use App\Models\ProcurementVendorPrice;
use App\Models\Setting;
use Spatie\Permission\Models\Role;

class ProcurementApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & admin
        $role = Role::firstOrCreate(['name' => 'Superadmin']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($role);
    }

    public function test_procurement_api_status_endpoint_returns_online(): void
    {
        $response = $this->getJson(route('api.v1.procurement.status'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'online',
                'service' => 'Herbatech R&D Procurement Integration API',
            ]);
    }

    public function test_procurement_api_rejects_unauthorized_token_when_token_is_configured(): void
    {
        Setting::updateOrCreate(['key' => 'procurement_api_token'], ['value' => 'secret_token_123']);

        $response = $this->postJson(route('api.v1.procurement.prices'), [
            'data' => [
                [
                    'material_name' => 'Jahe Merah Ekstrak',
                    'vendor_name' => 'PT Herbal Sejahtera',
                    'price_per_unit' => 150000,
                ]
            ]
        ], [
            'Authorization' => 'Bearer wrong_token'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_procurement_api_receives_and_maps_prices_successfully(): void
    {
        Setting::updateOrCreate(['key' => 'procurement_api_token'], ['value' => 'valid_token_xyz']);

        $material = Material::create([
            'name' => 'Ekstrak Temulawak Curcumin',
            'code' => 'MAT-TML-01',
            'unit' => 'kg',
        ]);

        $supplier = Supplier::create([
            'name' => 'PT Chemika Nusantara',
            'code' => 'VND-001',
        ]);

        $payload = [
            'data' => [
                [
                    'po_number' => 'PO-2026-9001',
                    'po_date' => '2026-10-15',
                    'material_name' => 'Ekstrak Temulawak Curcumin',
                    'vendor_name' => 'PT Chemika Nusantara',
                    'price_per_unit' => 220000,
                    'unit' => 'kg',
                    'currency' => 'IDR'
                ]
            ],
            'auto_apply' => false
        ];

        $response = $this->postJson(route('api.v1.procurement.prices'), $payload, [
            'Authorization' => 'Bearer valid_token_xyz'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'total_imported' => 1,
                    'total_mapped' => 1,
                    'total_applied' => 0,
                ]
            ]);

        $this->assertDatabaseHas('procurement_vendor_prices', [
            'po_number' => 'PO-2026-9001',
            'material_name' => 'Ekstrak Temulawak Curcumin',
            'vendor_name' => 'PT Chemika Nusantara',
            'price_per_unit' => 220000,
            'sync_status' => 'mapped',
            'matched_material_id' => $material->id,
            'matched_supplier_id' => $supplier->id,
        ]);
    }

    public function test_procurement_api_auto_apply_updates_material_supplier_and_records_history(): void
    {
        Setting::updateOrCreate(['key' => 'procurement_api_token'], ['value' => 'token_auto_apply']);

        $material = Material::create([
            'name' => 'Asam Sitrat Anhidrat Food Grade',
            'code' => 'MAT-CIT-01',
            'unit' => 'kg',
        ]);

        $supplier = Supplier::create([
            'name' => 'PT Brataco Chemical',
            'code' => 'VND-002',
        ]);

        $payload = [
            'data' => [
                [
                    'po_number' => 'PO-2026-9002',
                    'po_date' => '2026-10-16',
                    'material_name' => 'Asam Sitrat Anhidrat Food Grade',
                    'vendor_name' => 'PT Brataco Chemical',
                    'price_per_unit' => 45000,
                    'unit' => 'kg',
                    'currency' => 'IDR'
                ]
            ],
            'auto_apply' => true
        ];

        $response = $this->postJson(route('api.v1.procurement.prices'), $payload, [
            'Authorization' => 'Bearer token_auto_apply'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'total_applied' => 1,
                ]
            ]);

        $this->assertDatabaseHas('material_suppliers', [
            'material_id' => $material->id,
            'supplier_id' => $supplier->id,
            'current_price' => 45000,
            'price_source' => 'procurement_api',
        ]);

        $this->assertDatabaseHas('material_price_histories', [
            'new_price' => 45000,
            'source' => 'procurement_api',
        ]);

        $this->assertDatabaseHas('procurement_vendor_prices', [
            'po_number' => 'PO-2026-9002',
            'sync_status' => 'applied',
        ]);
    }

    public function test_material_supplier_price_lookup_api_returns_price(): void
    {
        $material = Material::create(['name' => 'Stevia Extract 95%', 'code' => 'MAT-STV-01', 'unit' => 'kg']);
        $supplier = Supplier::create(['name' => 'PT Natural Sweeteners', 'code' => 'VND-003']);

        MaterialSupplier::create([
            'material_id' => $material->id,
            'supplier_id' => $supplier->id,
            'current_price' => 850000,
            'currency' => 'IDR',
            'effective_date' => '2026-10-01',
            'price_source' => 'manual_rnd',
            'is_active' => true,
        ]);

        // Lookup with both material_id and supplier_id
        $response1 = $this->getJson(route('api.material-suppliers.price', [
            'material_id' => $material->id,
            'supplier_id' => $supplier->id,
        ]));

        $response1->assertStatus(200)
            ->assertJson([
                'found' => true,
                'price_per_kg' => 850000,
                'price_per_gram' => 850,
            ]);

        // Lookup with only material_id
        $response2 = $this->getJson(route('api.material-suppliers.price', [
            'material_id' => $material->id,
        ]));

        $response2->assertStatus(200)
            ->assertJson([
                'found' => true,
                'supplier_id' => $supplier->id,
                'price_per_kg' => 850000,
            ]);
    }

    public function test_user_can_manually_map_and_apply_procurement_price(): void
    {
        $material = Material::create(['name' => 'Kunyit Bubuk Ekstrak', 'code' => 'MAT-KNY-01', 'unit' => 'kg']);
        $supplier = Supplier::create(['name' => 'PT Agro Bio Kimia', 'code' => 'VND-004']);

        $price = ProcurementVendorPrice::create([
            'po_number' => 'PO-2026-8888',
            'po_date' => '2026-10-10',
            'material_name' => 'Kunyit Bubuk Raw Material',
            'vendor_name' => 'PT Agro Bio Kimia Int',
            'price_per_unit' => 95000,
            'unit' => 'kg',
            'currency' => 'IDR',
            'sync_status' => 'unmapped',
        ]);

        // Map manual
        $responseMap = $this->actingAs($this->adminUser)
            ->post(route('procurement-prices.map', $price), [
                'material_id' => $material->id,
                'supplier_id' => $supplier->id,
            ]);

        $responseMap->assertRedirect();
        $this->assertEquals('mapped', $price->fresh()->sync_status);
        $this->assertEquals($material->id, $price->fresh()->matched_material_id);

        // Apply ke R&D
        $responseApply = $this->actingAs($this->adminUser)
            ->post(route('procurement-prices.apply', $price));

        $responseApply->assertRedirect();
        $this->assertEquals('applied', $price->fresh()->sync_status);

        $this->assertDatabaseHas('material_suppliers', [
            'material_id' => $material->id,
            'supplier_id' => $supplier->id,
            'current_price' => 95000,
        ]);
    }

    public function test_batch_apply_applies_all_mapped_prices(): void
    {
        $material1 = Material::create(['name' => 'Madu Murni Randu', 'code' => 'MAT-MD-01', 'unit' => 'kg']);
        $supplier1 = Supplier::create(['name' => 'CV Lebah Nusantara', 'code' => 'VND-005']);

        $material2 = Material::create(['name' => 'Royal Jelly Powder', 'code' => 'MAT-RJ-01', 'unit' => 'kg']);
        $supplier2 = Supplier::create(['name' => 'CV Lebah Nusantara', 'code' => 'VND-005']);

        $p1 = ProcurementVendorPrice::create([
            'po_number' => 'PO-2026-7771',
            'material_name' => 'Madu Murni Randu',
            'vendor_name' => 'CV Lebah Nusantara',
            'price_per_unit' => 110000,
            'sync_status' => 'mapped',
            'matched_material_id' => $material1->id,
            'matched_supplier_id' => $supplier1->id,
        ]);

        $p2 = ProcurementVendorPrice::create([
            'po_number' => 'PO-2026-7772',
            'material_name' => 'Royal Jelly Powder',
            'vendor_name' => 'CV Lebah Nusantara',
            'price_per_unit' => 1800000,
            'sync_status' => 'mapped',
            'matched_material_id' => $material2->id,
            'matched_supplier_id' => $supplier2->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('procurement-prices.batch-apply'));

        $response->assertRedirect();
        $this->assertEquals('applied', $p1->fresh()->sync_status);
        $this->assertEquals('applied', $p2->fresh()->sync_status);

        $this->assertDatabaseHas('material_suppliers', [
            'material_id' => $material1->id,
            'supplier_id' => $supplier1->id,
            'current_price' => 110000,
        ]);

        $this->assertDatabaseHas('material_suppliers', [
            'material_id' => $material2->id,
            'supplier_id' => $supplier2->id,
            'current_price' => 1800000,
        ]);
    }
}
