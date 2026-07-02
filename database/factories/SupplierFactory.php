<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $suppliers = [
            'PT Alam Herbal Indonesia',
            'CV Sehat Alami',
            'PT Madu Hutan Nusantara',
            'CV Rempah Tradisional',
            'PT Herba Medika',
        ];

        return [
            'name' => fake()->randomElement($suppliers),
            'contact' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'address' => fake()->address(),
        ];
    }
}
