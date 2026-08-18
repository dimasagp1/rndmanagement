<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staff = User::where('email', 'staff@herbatech.com')->first();

        $products = [
            ['name' => 'Jahe Merah Hangat', 'description' => 'Minuman serbuk instan jahe merah dengan rasa hangat menenangkan.'],
            ['name' => 'Sari Kurma Madu', 'description' => 'Minuman sari kurma dicampur madu sebagai sumber energi.'],
            ['name' => 'Kunyit Asam Segar', 'description' => 'Minuman tradisional kunyit asam dengan rasa segar.'],
            ['name' => 'Ekstrak Temulawak Kapsul', 'description' => 'Suplemen kapsul ekstrak temulawak untuk menjaga daya tahan tubuh.'],
            ['name' => 'Teh Herbal Daun Jati Cina', 'description' => 'Teh herbal daun jati cina untuk kesehatan pencernaan.'],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                $product + ['created_by' => $staff?->id]
            );
        }
    }
}