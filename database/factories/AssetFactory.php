<?php

namespace Database\Factories;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Brand;
use App\Models\Location;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'name'              => fake()->words(3, true),
            'asset_category_id' => AssetCategory::factory(),
            'brand_id'          => Brand::factory(),
            'location_id'       => Location::factory(),
            'vendor_id'         => Vendor::factory(),
            'model'             => fake()->boolean(70) ? fake()->bothify('??-####') : null,
            'serial_number'     => fake()->boolean(70) ? fake()->unique()->bothify('SN-########') : null,
            'purchase_date'     => fake()->boolean(70) ? fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d') : null,
            'purchase_price'    => fake()->boolean(70) ? fake()->randomFloat(2, 100000, 50000000) : null,
            'quantity'          => fake()->numberBetween(1, 10),
            'status'            => fake()->randomElement(AssetStatus::cases())->value,
            'notes'             => fake()->boolean(50) ? fake()->sentence() : null,
            'mutation_date'     => null,
        ];
    }

    public function spare(): static
    {
        return $this->state(fn (array $attrs) => ['status' => AssetStatus::Spare->value]);
    }

    public function inUse(): static
    {
        return $this->state(fn (array $attrs) => ['status' => AssetStatus::InUse->value]);
    }
}
