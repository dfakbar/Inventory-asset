<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'username' => 'admin-dashboard',
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);

        $category = AssetCategory::create(['name' => 'Monitor', 'abbreviation' => 'MON']);

        Asset::create([
            'name'              => 'Asset A',
            'asset_category_id' => $category->id,
            'status'            => AssetStatus::InUse->value,
            'purchase_price'    => 5000000,
            'quantity'          => 1,
        ]);

        Asset::create([
            'name'              => 'Asset B',
            'asset_category_id' => $category->id,
            'status'            => AssetStatus::Spare->value,
            'purchase_price'    => 3000000,
            'quantity'          => 1,
        ]);
    }

    /** @test */
    public function dashboard_displays_correct_stats()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('2'); // total_assets
        $response->assertSee('Sedang Digunakan'); // InUse label
        $response->assertSee('Cadangan'); // Spare label
    }

    /** @test */
    public function dashboard_shows_latest_assets()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Asset A');
        $response->assertSee('Asset B');
    }
}
