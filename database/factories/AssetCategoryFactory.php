<?php

namespace Database\Factories;

use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetCategoryFactory extends Factory
{
    protected $model = AssetCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Monitor', 'Laptop', 'Keyboard', 'Mouse', 'Printer',
            'Server', 'Router', 'UPS', 'Projector', 'Scanner',
        ]);

        return [
            'name'         => $name,
            'abbreviation' => strtoupper(substr($name, 0, 3)),
            'description'  => fake()->optional()->sentence(),
        ];
    }
}
