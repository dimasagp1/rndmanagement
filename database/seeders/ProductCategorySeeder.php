<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'COD', 'description' => 'Cairan Obat Dalam.'],
            ['name' => 'COL', 'description' => 'Cairan Obat Luar.'],
            ['name' => 'Serbuk', 'description' => 'Sediaan serbuk/tepung.'],
            ['name' => 'Kapsul', 'description' => 'Sediaan kapsul keras maupun lunak.'],
        ];

        foreach ($categories as $category) {
            ProductCategory::updateOrCreate(['name' => $category['name']], $category);
        }

        // Hapus kategori lama yang tidak lagi dipakai
        $names = collect($categories)->pluck('name');
        ProductCategory::whereNotIn('name', $names)->delete();

        $this->command->info('✅ ' . ProductCategory::count() . ' Product Categories seeded.');
    }
}