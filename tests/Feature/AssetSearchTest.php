<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private AssetCategory $category;
    private Employee $employee;
    private Asset $assetWithEmployee;
    private Asset $assetWithoutEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'username' => 'admin-search',
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);

        $this->category = AssetCategory::create([
            'name'         => 'Laptop',
            'abbreviation' => 'LPT',
        ]);

        $this->employee = Employee::create(['name' => 'Budi Santoso']);

        $this->assetWithEmployee = Asset::create([
            'name'              => 'MacBook Pro',
            'asset_category_id' => $this->category->id,
            'status'            => AssetStatus::InUse->value,
            'quantity'          => 1,
            'employee_id'       => $this->employee->id,
        ]);

        $this->assetWithoutEmployee = Asset::create([
            'name'              => 'ThinkPad X1',
            'asset_category_id' => $this->category->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
        ]);
    }

    /** @test */
    public function asset_index_can_search_by_employee_name(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('assets.index', ['search' => 'Budi']));

        $response->assertStatus(200);
        $response->assertSee($this->assetWithEmployee->asset_code);
        $response->assertDontSee($this->assetWithoutEmployee->asset_code);
    }

    /** @test */
    public function asset_index_shows_not_found_for_unknown_employee_name(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('assets.index', ['search' => 'zzz-not-exist']));

        $response->assertStatus(200);
        $response->assertSee('Tidak Ditemukan');
    }
}
