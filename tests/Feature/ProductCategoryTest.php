<?php

namespace Tests\Feature;

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $staff;
    private User $gm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->superadmin = User::factory()->create(['email' => 'cat-sa@test.com']);
        $this->staff = User::factory()->create(['email' => 'cat-staff@test.com']);
        $this->gm = User::factory()->create(['email' => 'cat-gm@test.com']);
        $this->superadmin->assignRole('Superadmin');
        $this->staff->assignRole('Staff R&D');
        $this->gm->assignRole('General Manager');
    }

    public function test_superadmin_can_crud_product_categories(): void
    {
        $this->actingAs($this->superadmin)
            ->get('/product-categories')
            ->assertOk();

        $this->actingAs($this->superadmin)
            ->post('/product-categories', ['name' => 'Sediaan Cair'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('product_categories', ['name' => 'Sediaan Cair']);

        $category = ProductCategory::where('name', 'Sediaan Cair')->first();

        $this->actingAs($this->superadmin)
            ->put("/product-categories/{$category->id}", ['name' => 'Sediaan Cair V2'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('product_categories', ['name' => 'Sediaan Cair V2']);

        $this->actingAs($this->superadmin)
            ->delete("/product-categories/{$category->id}")
            ->assertRedirect();
        $this->assertDatabaseMissing('product_categories', ['name' => 'Sediaan Cair V2']);
    }

    public function test_duplicate_category_name_is_rejected(): void
    {
        ProductCategory::create(['name' => 'Kapsul']);

        $this->actingAs($this->superadmin)
            ->post('/product-categories', ['name' => 'Kapsul'])
            ->assertSessionHasErrors('name');
    }

    public function test_staff_rnd_can_access_but_gm_cannot(): void
    {
        $this->actingAs($this->staff)->get('/product-categories')->assertOk();
        $this->actingAs($this->gm)->get('/product-categories')->assertForbidden();
    }

    public function test_prf_form_lists_categories_in_select(): void
    {
        ProductCategory::create(['name' => 'Tablet']);

        $staff = User::factory()->create(['email' => 'prf-cat@test.com']);
        $staff->assignRole('Staff R&D');

        $this->actingAs($staff)
            ->get('/prfs/create')
            ->assertOk()
            ->assertSee('Tablet')
            ->assertSee('product_category');
    }
}