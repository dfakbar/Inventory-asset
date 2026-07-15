<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Ruang Server', 'Gudang IT', 'Lantai 1 - Timur', 'Lantai 2 - Barat',
            'Ruang Meeting A', 'Ruang Meeting B', 'Lobby Utama', 'Ruang HRD',
        ]);

        return [
            'name'        => $name,
            'department'  => fake()->optional()->randomElement(['IT', 'HRD', 'Finance', 'Marketing', 'Operational']),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
