<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Using Asset::create() instead of factory (no AssetFactory exists)

class QrBarcodeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'username' => 'admin-qr',
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);

        $category = AssetCategory::create(['name' => 'Monitor', 'abbreviation' => 'MON']);

        $this->asset = Asset::create([
            'name'              => 'QR Test Asset',
            'asset_category_id' => $category->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
        ]);
    }

    /** @test */
    public function qr_code_returns_svg()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('assets.qr-code', $this->asset));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $response->assertSee('<svg', false);
    }

    /** @test */
    public function barcode_returns_svg()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('assets.barcode', $this->asset));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $response->assertSee('<svg', false);
    }

    /** @test */
    public function print_code_page_renders()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('assets.print-code', [$this->asset, 'type' => 'qr', 'count' => 4]));

        $response->assertStatus(200);
        $response->assertSee($this->asset->asset_code);
    }
}
