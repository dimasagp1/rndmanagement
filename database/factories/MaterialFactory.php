<?php

namespace Database\Factories;

use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $herbalMaterials = [
            ['name' => 'Ekstrak Jahe', 'type' => 'Ekstrak', 'unit' => 'kg'],
            ['name' => 'Madu Murni', 'type' => 'Cairan', 'unit' => 'liter'],
            ['name' => 'Ekstrak Kunyit', 'type' => 'Ekstrak', 'unit' => 'kg'],
            ['name' => 'Temulawak Bubuk', 'type' => 'Bubuk', 'unit' => 'kg'],
            ['name' => 'Daun Mint Kering', 'type' => 'Bubuk', 'unit' => 'kg'],
            ['name' => 'Ekstrak Lidah Buaya', 'type' => 'Ekstrak', 'unit' => 'liter'],
            ['name' => 'Gula Aren', 'type' => 'Pemanis', 'unit' => 'kg'],
            ['name' => 'Asam Jawa', 'type' => 'Perasa', 'unit' => 'kg'],
            ['name' => 'Kayu Manis Bubuk', 'type' => 'Bubuk', 'unit' => 'kg'],
            ['name' => 'Air Mineral', 'type' => 'Pelarut', 'unit' => 'liter'],
        ];

        $material = fake()->randomElement($herbalMaterials);

        return [
            'name' => $material['name'],
            'type' => $material['type'],
            'unit' => $material['unit'],
            'description' => fake()->sentence(),
        ];
    }
}
