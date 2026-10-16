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
use App\Models\Setting;
use Spatie\Permission\Models\Role;

class ProcurementOutboundPullSyncTest extends TestCase
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

    public function test_rnd_can_pull_material_prices_from_procurement_api_successfully(): void
    {
        $apiUrl = 'http://procurement.test/api/v1/procurement/material-prices';
        $apiKey = 'bsc_sec_test_key_123';

        Setting::updateOrCreate(['key' => 'procurement_api_url'], ['value' => $apiUrl]);
        Setting::updateOrCreate(['key' => 'procurement_api_token'], ['value' => $apiKey]);
        Setting::updateOrCreate(['key' => 'procurement_auto_apply'], ['value' => '0']);

        $material = Material::create(['name' => 'Ekstrak Meniran Hijau', 'code' => 'MAT-MNR-01', 'unit' => 'kg']);
        $supplier = Supplier::create(['name' => 'PT Herbal Bio Alami', 'code' => 'VND-HBA']);

        Http::fake([
            $apiUrl => Http::response([
                'success' => true,
                'total' => 1,
                'data' => [
                    [
                        'material_code'  => 'MAT-MNR-01',
                        'material_name'  => 'Ekstrak Meniran Hijau',
                        'vendor_code'    => 'VND-HBA',
                        'vendor_name'    => 'PT Herbal Bio Alami',
                        'price_per_unit' => 310000,
                        'unit'           => 'kg',
                        'currency'       => 'IDR',
                        'po_number'      => 'PO-2026-5541',
                        'po_date'        => '2026-10-15',
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('procurement-prices.sync'));

        $response->assertRedirect(route('procurement-prices.index'))
            ->assertSessionHas('success');

        Http::assertSent(function ($request) use ($apiUrl, $apiKey) {
            return $request->url() === $apiUrl &&
                   $request->hasHeader('X-API-KEY', $apiKey);
        });

        $this->assertDatabaseHas('procurement_vendor_prices', [
            'po_number'           => 'PO-2026-5541',
            'material_name'       => 'Ekstrak Meniran Hijau',
            'vendor_name'         => 'PT Herbal Bio Alami',
            'price_per_unit'      => 310000,
            'sync_status'         => 'mapped',
            'matched_material_id' => $material->id,
            'matched_supplier_id' => $supplier->id,
        ]);
    }

    public function test_rnd_pull_handles_item_standard_price_naming_convention(): void
    {
        $apiUrl = 'http://procurement.test/api/v1/procurement/material-prices';
        Setting::updateOrCreate(['key' => 'procurement_api_url'], ['value' => $apiUrl]);

        $material = Material::create(['name' => 'Maltodextrin DE 10-12', 'code' => 'MAT-MLT-01', 'unit' => 'kg']);
        $supplier = Supplier::create(['name' => 'PT Pangan Manis Indo', 'code' => 'VND-PMI']);

        // Format data native dari model ItemStandardPrice procurementversiA
        Http::fake([
            $apiUrl => Http::response([
                'data' => [
                    [
                        'item_code'      => 'MAT-MLT-01',
                        'item_name'      => 'Maltodextrin DE 10-12',
                        'vendor_name'    => 'PT Pangan Manis Indo',
                        'standard_price' => 28500,
                        'uom'            => 'kg',
                        'po_number'      => 'PO-2026-1190',
                        'po_date'        => '2026-10-12',
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('procurement-prices.sync'));

        $response->assertRedirect(route('procurement-prices.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('procurement_vendor_prices', [
            'material_name'  => 'Maltodextrin DE 10-12',
            'vendor_name'    => 'PT Pangan Manis Indo',
            'price_per_unit' => 28500,
            'unit'           => 'kg',
            'sync_status'    => 'mapped',
        ]);
    }

    public function test_rnd_pull_handles_procurement_api_connection_error_gracefully(): void
    {
        $apiUrl = 'http://procurement.test/api/v1/procurement/material-prices';
        Setting::updateOrCreate(['key' => 'procurement_api_url'], ['value' => $apiUrl]);

        Http::fake([
            $apiUrl => Http::response(['message' => 'Unauthorized'], 401)
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('procurement-prices.sync'));

        $response->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_rnd_pull_auto_applies_prices_when_enabled_in_settings(): void
    {
        $apiUrl = 'http://procurement.test/api/v1/procurement/material-prices';
        Setting::updateOrCreate(['key' => 'procurement_api_url'], ['value' => $apiUrl]);
        Setting::updateOrCreate(['key' => 'procurement_auto_apply'], ['value' => '1']); // AUTO-APPLY ON

        $material = Material::create(['name' => 'Spirulina Powder Organic', 'code' => 'MAT-SPR-01', 'unit' => 'kg']);
        $supplier = Supplier::create(['name' => 'PT Alga Nusantara', 'code' => 'VND-ALGA']);

        Http::fake([
            $apiUrl => Http::response([
                'data' => [
                    [
                        'material_name'  => 'Spirulina Powder Organic',
                        'vendor_name'    => 'PT Alga Nusantara',
                        'price_per_unit' => 450000,
                        'unit'           => 'kg',
                        'currency'       => 'IDR',
                        'po_number'      => 'PO-2026-9999',
                        'po_date'        => '2026-10-16',
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('procurement-prices.sync'));

        $response->assertRedirect(route('procurement-prices.index'))
            ->assertSessionHas('success');

        // Memastikan langsung masuk ke katalog R&D
        $this->assertDatabaseHas('material_suppliers', [
            'material_id'   => $material->id,
            'supplier_id'   => $supplier->id,
            'current_price' => 450000,
            'price_source'  => 'procurement_api',
        ]);

        // Memastikan tercatat di log riwayat mutasi harga
        $this->assertDatabaseHas('material_price_histories', [
            'new_price' => 450000,
            'source'    => 'procurement_api',
        ]);

        // Status di feed staging langsung 'applied'
        $this->assertDatabaseHas('procurement_vendor_prices', [
            'po_number'   => 'PO-2026-9999',
            'sync_status' => 'applied',
        ]);
    }
}
