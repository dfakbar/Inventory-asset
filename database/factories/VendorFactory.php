<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'name'           => fake()->unique()->company(),
            'contact_person' => fake()->name(),
            'phone'          => fake()->phoneNumber(),
            'email'          => fake()->companyEmail(),
            'address'        => fake()->address(),
            'description'    => fake()->optional()->sentence(),
        ];
    }
}
