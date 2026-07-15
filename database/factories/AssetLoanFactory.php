<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetLoanFactory extends Factory
{
    protected $model = AssetLoan::class;

    public function definition(): array
    {
        return [
            'asset_id'             => Asset::factory(),
            'borrower_name'        => fake()->name(),
            'borrower_email'       => fake()->optional()->email(),
            'loan_date'            => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'expected_return_date' => fake()->optional()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'returned_at'          => null,
            'notes'                => fake()->optional()->sentence(),
            'created_by'           => User::factory(),
        ];
    }

    public function returned(): static
    {
        return $this->state(fn (array $attrs) => [
            'returned_at' => fake()->dateTimeBetween('-15 days', 'now')->format('Y-m-d'),
        ]);
    }
}
