<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Brand;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkUpdateAssetTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $mutationOnlyUser;
    private AssetCategory $category;
    private Location $location;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->adminUser = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'username' => 'admin-user',
            'role'     => UserRole::Admin,
        ]);
        $this->adminUser->assignRole(UserRole::Admin->value);

        $this->mutationOnlyUser = User::create([
            'name'     => 'Mutation Staff',
            'email'    => 'mutation@example.com',
            'password' => bcrypt('password'),
            'username' => 'mutation-staff',
            'role'     => UserRole::Staff,
        ]);
        $this->mutationOnlyUser->assignRole(UserRole::Staff->value);
        $this->mutationOnlyUser->givePermissionTo(['asset.viewAny', 'asset.mutate']);

        $this->category = AssetCategory::create([
            'name'         => 'Test Category',
            'abbreviation' => 'TST',
        ]);

        $this->location = Location::create(['name' => 'Test Location']);

        $this->employee = Employee::create([
            'name'      => 'Test Employee',
            'is_active' => true,
        ]);
    }

    private function createAsset(string $name): Asset
    {
        return Asset::create([
            'name'              => $name,
            'asset_category_id' => $this->category->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
            'assigned_to'       => $this->adminUser->id,
        ]);
    }

    /** @test */
    public function admin_can_bulk_update_status_and_location_of_multiple_assets(): void
    {
        $assetA = $this->createAsset('Bulk A');
        $assetB = $this->createAsset('Bulk B');

        $response = $this->actingAs($this->adminUser)
            ->post(route('assets.bulk-update'), [
                'ids'         => [$assetA->id, $assetB->id],
                'status'      => AssetStatus::InUse->value,
                'location_id' => $this->location->id,
            ], ['Accept' => 'application/json']);

        $response->assertOk();

        $this->assertDatabaseHas('assets', [
            'id'          => $assetA->id,
            'status'      => AssetStatus::InUse->value,
            'location_id' => $this->location->id,
        ]);
        $this->assertDatabaseHas('assets', [
            'id'          => $assetB->id,
            'status'      => AssetStatus::InUse->value,
            'location_id' => $this->location->id,
        ]);
    }

    /** @test */
    public function admin_can_bulk_update_identity_fields(): void
    {
        $brand = Brand::create(['name' => 'Bulk Brand']);
        $asset = $this->createAsset('Bulk Identity');

        $response = $this->actingAs($this->adminUser)
            ->post(route('assets.bulk-update'), [
                'ids'      => [$asset->id],
                'brand_id' => $brand->id,
                'model'    => 'Bulk Model',
            ], ['Accept' => 'application/json']);

        $response->assertOk();

        $this->assertDatabaseHas('assets', [
            'id'       => $asset->id,
            'brand_id' => $brand->id,
            'model'    => 'Bulk Model',
        ]);
    }

    /** @test */
    public function admin_can_bulk_update_purchase_price_and_purchase_date(): void
    {
        $asset = $this->createAsset('Bulk Purchase');

        $response = $this->actingAs($this->adminUser)
            ->post(route('assets.bulk-update'), [
                'ids'            => [$asset->id],
                'purchase_price' => 2500000,
                'purchase_date'  => '2026-01-15',
            ], ['Accept' => 'application/json']);

        $response->assertOk();

        $this->assertDatabaseHas('assets', [
            'id'             => $asset->id,
            'purchase_price' => 2500000,
            'purchase_date'  => '2026-01-15 00:00:00',
        ]);
    }

    /** @test */
    public function mutation_only_user_is_restricted_to_mutation_fields(): void
    {
        $brand = Brand::create(['name' => 'Sneaky Brand']);
        $asset = $this->createAsset('Bulk Restricted');

        $response = $this->actingAs($this->mutationOnlyUser)
            ->post(route('assets.bulk-update'), [
                'ids'            => [$asset->id],
                'status'         => AssetStatus::InUse->value,
                'employee_id'    => $this->employee->id,
                'brand_id'       => $brand->id,
                'model'          => 'Should Be Ignored',
                'purchase_price' => 999999,
                'purchase_date'  => '2026-02-20',
            ], ['Accept' => 'application/json']);

        $response->assertOk();

        $this->assertDatabaseHas('assets', [
            'id'          => $asset->id,
            'status'      => AssetStatus::InUse->value,
            'employee_id' => $this->employee->id,
            'brand_id'    => null,
            'model'       => null,
            'purchase_price' => null,
            'purchase_date'  => null,
        ]);
    }

    /** @test */
    public function bulk_update_requires_at_least_one_changed_field(): void
    {
        $asset = $this->createAsset('Bulk Noop');

        $response = $this->actingAs($this->adminUser)
            ->post(route('assets.bulk-update'), [
                'ids' => [$asset->id],
            ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_bulk_update(): void
    {
        $asset = $this->createAsset('Bulk Unauth');

        $response = $this->post(route('assets.bulk-update'), [
            'ids'    => [$asset->id],
            'status' => AssetStatus::InUse->value,
        ]);

        $response->assertRedirect(route('login'));
    }
}
