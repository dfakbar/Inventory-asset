<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Brand;
use App\Models\Location;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
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
            'username' => 'admin-report',
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);
    }

    /** @test */
    public function admin_can_view_reports_index()
    {
        $response = $this->actingAs($this->admin)->get(route('reports.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_download_assets_pdf()
    {
        $category = AssetCategory::create(['name' => 'Monitor', 'abbreviation' => 'MON']);
        Brand::create(['name' => 'Dell']);
        Location::create(['name' => 'Ruang IT']);
        Vendor::create(['name' => 'PT Supplier']);

        Asset::create([
            'name'              => 'PDF Asset',
            'asset_category_id' => $category->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
        ]);

        $response = $this->actingAs($this->admin)->get(route('reports.assets-pdf'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function admin_can_download_categories_pdf()
    {
        AssetCategory::create(['name' => 'Monitor', 'abbreviation' => 'MON']);

        $response = $this->actingAs($this->admin)->get(route('reports.categories-pdf'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function assets_pdf_can_filter_by_status()
    {
        $category = AssetCategory::create(['name' => 'Monitor', 'abbreviation' => 'MON']);

        Asset::create([
            'name'              => 'In Use Asset',
            'asset_category_id' => $category->id,
            'status'            => AssetStatus::InUse->value,
            'quantity'          => 1,
        ]);

        $response = $this->actingAs($this->admin)->get(route('reports.assets-pdf', [
            'status' => AssetStatus::InUse->value,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
