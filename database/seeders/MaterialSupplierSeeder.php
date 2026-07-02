<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;
use App\Models\Supplier;

class MaterialSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Materials - 10 bahan herbal
        $materials = [
            ['name' => 'Ekstrak Jahe', 'type' => 'Ekstrak', 'unit' => 'kg', 'description' => 'Ekstrak jahe merah premium untuk produk herbal'],
            ['name' => 'Madu Murni', 'type' => 'Cairan', 'unit' => 'liter', 'description' => 'Madu hutan asli tanpa campuran'],
            ['name' => 'Ekstrak Kunyit', 'type' => 'Ekstrak', 'unit' => 'kg', 'description' => 'Ekstrak kunyit dengan curcumin tinggi'],
            ['name' => 'Temulawak Bubuk', 'type' => 'Bubuk', 'unit' => 'kg', 'description' => 'Temulawak bubuk kering kualitas farmasi'],
            ['name' => 'Daun Mint Kering', 'type' => 'Bubuk', 'unit' => 'kg', 'description' => 'Daun mint kering untuk aroma segar'],
            ['name' => 'Ekstrak Lidah Buaya', 'type' => 'Ekstrak', 'unit' => 'liter', 'description' => 'Ekstrak lidah buaya organik'],
            ['name' => 'Gula Aren', 'type' => 'Pemanis', 'unit' => 'kg', 'description' => 'Gula aren organik sebagai pemanis alami'],
            ['name' => 'Asam Jawa', 'type' => 'Perasa', 'unit' => 'kg', 'description' => 'Asam jawa untuk rasa segar'],
            ['name' => 'Kayu Manis Bubuk', 'type' => 'Bubuk', 'unit' => 'kg', 'description' => 'Kayu manis bubuk cassia premium'],
            ['name' => 'Air Mineral', 'type' => 'Pelarut', 'unit' => 'liter', 'description' => 'Air mineral murni sebagai pelarut'],
        ];

        foreach ($materials as $material) {
            Material::create($material);
        }

        $this->command->info('✅ ' . count($materials) . ' Materials seeded');

        // Seed Suppliers - 5 supplier
        $suppliers = [
            [
                'name' => 'PT Alam Herbal Indonesia',
                'contact' => 'Pak Surya',
                'phone' => '021-5551234',
                'email' => 'sales@alamherbal.co.id',
                'address' => 'Jl. Raya Bogor No. 123, Jakarta Timur'
            ],
            [
                'name' => 'CV Sehat Alami',
                'contact' => 'Ibu Dewi',
                'phone' => '021-5555678',
                'email' => 'info@sehatalami.com',
                'address' => 'Jl. Sudirman No. 45, Bogor'
            ],
            [
                'name' => 'PT Madu Hutan Nusantara',
                'contact' => 'Pak Anto',
                'phone' => '0251-888999',
                'email' => 'order@maduhutan.id',
                'address' => 'Jl. Raya Sukabumi KM 12, Bogor'
            ],
            [
                'name' => 'CV Rempah Tradisional',
                'contact' => 'Pak Budi',
                'phone' => '022-7776543',
                'email' => 'sales@rempahtrad.co.id',
                'address' => 'Jl. Kopo No. 89, Bandung'
            ],
            [
                'name' => 'PT Herba Medika',
                'contact' => 'Ibu Sari',
                'phone' => '024-6665432',
                'email' => 'info@herbamedika.com',
                'address' => 'Jl. Pemuda No. 67, Semarang'
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        $this->command->info('✅ ' . count($suppliers) . ' Suppliers seeded');
        $this->command->info('📦 Master data ready for production!');
    }
}
