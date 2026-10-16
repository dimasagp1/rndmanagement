<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\MaterialSupplier;
use App\Models\ProcurementVendorPrice;
use App\Models\ProcurementMappingAlias;
use App\Models\Setting;
use Spatie\Permission\Models\Role;

class ProcurementMappingMemoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'Superadmin']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($role);
    }

    /**
     * Test bahwa ketika user memetakan item, pemetaan disimpan ke memory alias
     * dan otomatis memetakan baris PO unmapped lain yang bernama sama.
     */
    public function test_manual_mapping_saves_to_memory_and_propagates_to_similar_unmapped_rows(): void
    {
        $material = Material::create(['name' => 'Oat Powder', 'code' => 'MAT-OAT-01', 'unit' => 'kg']);
        $supplier = Supplier::create(['name' => 'PT Pangan Agung Sejahtera', 'code' => 'VND-PAS']);

        // 2 PO dengan nama bahan & vendor yang sama dari Procurement
        $price1 = ProcurementVendorPrice::create([
            'po_number'     => 'PO-007/HERBATECH/06/2025',
            'material_name' => 'Oat Powder ex Custom Food',
            'vendor_name'   => 'PT PANGAN AGUNG SEJAHTERA',
            'price_per_unit'=> 48000,
            'unit'          => 'kg',
            'sync_status'   => 'unmapped',
        ]);

        $price2 = ProcurementVendorPrice::create([
            'po_number'     => 'PO-018/HERBATECH/08/2025',
            'material_name' => 'Oat Powder ex Custom Food',
            'vendor_name'   => 'PT PANGAN AGUNG SEJAHTERA',
            'price_per_unit'=> 50000,
            'unit'          => 'kg',
            'sync_status'   => 'unmapped',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('procurement-prices.map', $price1), [
                'material_id' => $material->id,
                'supplier_id' => $supplier->id,
                'propagate'   => 1,
            ]);

        $response->assertRedirect();

        // 1. Cek bahwa price1 terpetakan
        $this->assertEquals('mapped', $price1->fresh()->sync_status);
        $this->assertEquals($material->id, $price1->fresh()->matched_material_id);
        $this->assertEquals($supplier->id, $price1->fresh()->matched_supplier_id);

        // 2. Cek bahwa price2 otomatis ikut terpetakan berkat propagasi
        $this->assertEquals('mapped', $price2->fresh()->sync_status);
        $this->assertEquals($material->id, $price2->fresh()->matched_material_id);
        $this->assertEquals($supplier->id, $price2->fresh()->matched_supplier_id);

        // 3. Cek bahwa relasi tersimpan di memory alias
        $this->assertDatabaseHas('procurement_mapping_aliases', [
            'source_type' => 'material',
            'source_name' => 'Oat Powder ex Custom Food',
            'target_id'   => $material->id,
        ]);
        $this->assertDatabaseHas('procurement_mapping_aliases', [
            'source_type' => 'supplier',
            'source_name' => 'PT PANGAN AGUNG SEJAHTERA',
            'target_id'   => $supplier->id,
        ]);
    }

    /**
     * Test bahwa ketika ada penarikan data baru dari API (ada update harga di masa depan),
     * sistem otomatis memetakan data tersebut berkat memory alias terdahulu.
     */
    public function test_subsequent_pull_sync_automatically_maps_based_on_memory(): void
    {
        $material = Material::create(['name' => 'Oat Powder', 'code' => 'MAT-OAT-01', 'unit' => 'kg']);
        $supplier = Supplier::create(['name' => 'PT Pangan Agung Sejahtera', 'code' => 'VND-PAS']);

        // Simpan memory alias terdahulu
        ProcurementMappingAlias::create([
            'source_type' => 'material',
            'source_name' => 'Oat Powder ex Custom Food',
            'target_id'   => $material->id,
        ]);
        ProcurementMappingAlias::create([
            'source_type' => 'supplier',
            'source_name' => 'PT PANGAN AGUNG SEJAHTERA',
            'target_id'   => $supplier->id,
        ]);

        $apiUrl = 'http://procurement.test/api/v1/procurement/material-prices';
        Setting::updateOrCreate(['key' => 'procurement_api_url'], ['value' => $apiUrl]);
        Setting::updateOrCreate(['key' => 'procurement_api_token'], ['value' => 'test_token']);
        Setting::updateOrCreate(['key' => 'procurement_auto_apply'], ['value' => '0']);

        // Tarik data baru dengan harga update (Rp 52.000)
        Http::fake([
            $apiUrl => Http::response([
                'success' => true,
                'data' => [
                    [
                        'po_number'      => 'PO-2026-9999',
                        'po_date'        => '2026-10-16',
                        'material_name'  => 'Oat Powder ex Custom Food',
                        'vendor_name'    => 'PT PANGAN AGUNG SEJAHTERA',
                        'price_per_unit' => 52000,
                        'unit'           => 'kg',
                        'currency'       => 'IDR',
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('procurement-prices.sync'));

        $response->assertRedirect(route('procurement-prices.index'));

        // Cek bahwa data baru langsung berstatus 'mapped' secara otomatis tanpa perlu map manual!
        $newRecord = ProcurementVendorPrice::where('po_number', 'PO-2026-9999')->first();
        $this->assertNotNull($newRecord);
        $this->assertEquals('mapped', $newRecord->sync_status);
        $this->assertEquals($material->id, $newRecord->matched_material_id);
        $this->assertEquals($supplier->id, $newRecord->matched_supplier_id);
        $this->assertEquals(52000, (float) $newRecord->price_per_unit);
    }

    /**
     * Test Auto-Map All secara massal pada data unmapped yang sudah ada.
     */
    public function test_auto_map_all_matches_existing_unmapped_rows(): void
    {
        $material = Material::create(['name' => 'Almond Powder', 'code' => 'MAT-ALM-01', 'unit' => 'kg']);
        $supplier = Supplier::create(['name' => 'PT Gunanusa Eramandiri', 'code' => 'VND-GNE']);

        ProcurementMappingAlias::create([
            'source_type' => 'material',
            'source_name' => 'Almond Milk Powder Less Sugar',
            'target_id'   => $material->id,
        ]);
        ProcurementMappingAlias::create([
            'source_type' => 'supplier',
            'source_name' => 'PT Gunanusa Eramandiri',
            'target_id'   => $supplier->id,
        ]);

        $unmappedRow = ProcurementVendorPrice::create([
            'po_number'     => 'PO-015/HERBATECH/06/2026',
            'material_name' => 'Almond Milk Powder Less Sugar',
            'vendor_name'   => 'PT Gunanusa Eramandiri',
            'price_per_unit'=> 161000,
            'unit'          => 'kg',
            'sync_status'   => 'unmapped',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('procurement-prices.auto-map'));

        $response->assertRedirect();
        $this->assertEquals('mapped', $unmappedRow->fresh()->sync_status);
        $this->assertEquals($material->id, $unmappedRow->fresh()->matched_material_id);
        $this->assertEquals($supplier->id, $unmappedRow->fresh()->matched_supplier_id);
    }
}
