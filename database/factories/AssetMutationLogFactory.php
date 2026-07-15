<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetMutationLog;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetMutationLogFactory extends Factory
{
    protected $model = AssetMutationLog::class;

    public function definition(): array
    {
        return [
            'asset_id'         => Asset::factory(),
            'performed_by'     => User::factory(),
            'from_location_id' => Location::factory(),
            'to_location_id'   => Location::factory(),
            'from_assigned_to' => null,
            'to_assigned_to'   => null,
            'from_status'      => fake()->randomElement(['Spare', 'In Use']),
            'to_status'        => fake()->randomElement(['In Use', 'Service', 'Broken']),
            'mutation_date'    => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'notes'            => fake()->optional()->sentence(),
        ];
    }
}
