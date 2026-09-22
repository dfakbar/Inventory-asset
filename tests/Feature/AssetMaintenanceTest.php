<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMaintenance;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Asset $asset;

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

        $category = AssetCategory::create([
            'name'         => 'Laptop',
            'abbreviation' => 'LPT',
        ]);

        $this->asset = Asset::create([
            'name'              => 'MacBook Pro',
            'asset_category_id' => $category->id,
            'status'            => AssetStatus::InUse->value,
            'quantity'          => 1,
        ]);
    }

    /** @test */
    public function admin_can_add_maintenance_record_to_asset(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('assets.maintenances.store', $this->asset), [
                'action_type'      => 'addition',
                'component_name'   => 'RAM',
                'previous_spec'    => '8GB DDR4',
                'new_spec'         => '16GB DDR4',
                'cost'             => 750000,
                'maintenance_date' => '2026-09-06',
                'notes'            => 'Upgrade RAM dual channel',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('asset_maintenances', [
            'asset_id'       => $this->asset->id,
            'action_type'    => 'addition',
            'component_name' => 'RAM',
            'previous_spec'  => '8GB DDR4',
            'new_spec'       => '16GB DDR4',
            'cost'           => 750000.00,
        ]);

        $maintenance = AssetMaintenance::first();
        $this->assertEquals($this->adminUser->id, $maintenance->performed_by);
    }

    /** @test */
    public function admin_can_delete_maintenance_record(): void
    {
        $maintenance = AssetMaintenance::create([
            'asset_id'         => $this->asset->id,
            'performed_by'     => $this->adminUser->id,
            'action_type'      => 'reduction',
            'component_name'   => 'Battery',
            'new_spec'         => 'Removed faulty battery',
            'maintenance_date' => '2026-09-06',
        ]);

        $this->assertDatabaseHas('asset_maintenances', ['id' => $maintenance->id]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('assets.maintenances.destroy', [$this->asset, $maintenance]));

        $response->assertRedirect();

        $this->assertSoftDeleted('asset_maintenances', ['id' => $maintenance->id]);
    }
}
