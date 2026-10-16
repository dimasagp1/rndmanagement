<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Setting;
use Spatie\Permission\Models\Role;

class SupplierProcurementSyncTest extends TestCase
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

    public function test_setting_can_test_procurement_api_connection_successfully(): void
    {
        $apiUrl = 'http://procurement.test/api/v1/procurement/material-prices';
        $apiKey = 'bsc_sec_live_9f82d1c6b3e44a7b';

        Http::fake([
            $apiUrl => Http::response([
                'success' => true,
                'total'   => 2,
                'data'    => [
                    [
                        'material_name'  => 'Ekstrak Temulawak Curcumin',
                        'vendor_name'    => 'PT Chemika Nusantara',
                        'price_per_unit' => 220000,
                    ],
                    [
                        'material_name'  => 'Minyak Habbatussauda Pure',
                        'vendor_name'    => 'CV Herbal Berkah Abadi',
                        'price_per_unit' => 195000,
                    ],
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('settings.test-procurement-api'), [
                'url'   => $apiUrl,
                'token' => $apiKey,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'    => true,
                'status'     => 200,
                'total_data' => 2,
            ]);
    }

    public function test_setting_test_procurement_api_returns_error_when_url_is_empty(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('settings.test-procurement-api'), [
                'url' => '',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_setting_test_procurement_api_handles_remote_error(): void
    {
        $apiUrl = 'http://procurement.test/api/v1/procurement/material-prices';

        Http::fake([
            $apiUrl => Http::response(['message' => 'Unauthorized'], 401)
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('settings.test-procurement-api'), [
                'url'   => $apiUrl,
                'token' => 'wrong_token',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'status'  => 401,
            ]);
    }

    public function test_suppliers_can_be_synced_from_procurement_api(): void
    {
        $apiUrl = 'http://procurement.test/api/v1/procurement/material-prices';
        Setting::updateOrCreate(['key' => 'procurement_api_url'], ['value' => $apiUrl]);
        Setting::updateOrCreate(['key' => 'procurement_api_token'], ['value' => 'valid_token']);

        // Vendor yang sudah ada
        Supplier::create(['name' => 'PT Brataco Chemical']);

        Http::fake([
            $apiUrl => Http::response([
                'data' => [
                    [
                        'vendor_code' => 'VND-001',
                        'vendor_name' => 'PT Chemika Nusantara', // Baru
                    ],
                    [
                        'vendor_code' => 'VND-002',
                        'vendor_name' => 'PT Brataco Chemical', // Sudah ada
                    ],
                    [
                        'vendor_code' => 'VND-003',
                        'vendor_name' => 'CV Sentra Inovasi Kelapa', // Baru
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('suppliers.sync-procurement'));

        $response->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('success');

        // Memastikan vendor baru masuk ke master data suppliers
        $this->assertDatabaseHas('suppliers', [
            'name' => 'PT Chemika Nusantara',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'CV Sentra Inovasi Kelapa',
        ]);

        // Total supplier seharusnya 3
        $this->assertEquals(3, Supplier::count());
    }
}
