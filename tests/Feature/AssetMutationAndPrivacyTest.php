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

class AssetMutationAndPrivacyTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $staffUser;
    private AssetCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Run permission seeder
        $this->seed(PermissionSeeder::class);

        // Create Admin
        $this->adminUser = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'role'     => UserRole::Admin,
        ]);
        $this->adminUser->assignRole(UserRole::Admin->value);

        // Create Staff
        $this->staffUser = User::create([
            'name'     => 'Staff User',
            'email'    => 'staff@example.com',
            'password' => bcrypt('password'),
            'role'     => UserRole::Staff,
        ]);
        $this->staffUser->assignRole(UserRole::Staff->value);
        // Give staff edit/create permissions for testing (default staff in these tests has edit access)
        $this->staffUser->givePermissionTo(['asset.viewAny', 'asset.create', 'asset.edit']);

        // Create Category
        $this->category = AssetCategory::create([
            'name'         => 'Test Category',
            'abbreviation' => 'TST',
        ]);
    }

    /** @test */
    public function admin_can_create_asset_with_mutation_date_and_purchase_price(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('assets.store'), [
                'name'              => 'New Asset Admin',
                'asset_category_id' => $this->category->id,
                'status'            => AssetStatus::Spare->value,
                'quantity'          => 1,
                'purchase_date'     => '2026-06-01',
                'mutation_date'     => '2026-06-15',
                'purchase_price'    => 1500000.00,
            ]);

        $response->assertRedirect();
        
        $asset = Asset::where('name', 'New Asset Admin')->first();
        $this->assertNotNull($asset);
        $this->assertEquals('2026-06-15', $asset->mutation_date->format('Y-m-d'));
        $this->assertEquals(1500000.00, $asset->purchase_price);
    }

    /** @test */
    public function staff_can_create_asset_with_mutation_date_but_purchase_price_is_ignored(): void
    {
        $response = $this->actingAs($this->staffUser)
            ->post(route('assets.store'), [
                'name'              => 'New Asset Staff',
                'asset_category_id' => $this->category->id,
                'status'            => AssetStatus::Spare->value,
                'quantity'          => 1,
                'purchase_date'     => '2026-06-01',
                'mutation_date'     => '2026-06-15',
                'purchase_price'    => 2000000.00,
            ]);

        $response->assertRedirect();

        $asset = Asset::where('name', 'New Asset Staff')->first();
        $this->assertNotNull($asset);
        $this->assertEquals('2026-06-15', $asset->mutation_date->format('Y-m-d'));
        $this->assertNull($asset->purchase_price); // Should be ignored/unset and therefore null
    }

    /** @test */
    public function staff_with_finances_permission_can_create_asset_with_mutation_date_and_purchase_price(): void
    {
        $privilegedStaff = User::create([
            'name'     => 'Privileged Staff',
            'email'    => 'privstaff@example.com',
            'password' => bcrypt('password'),
            'role'     => UserRole::Staff,
        ]);
        $privilegedStaff->assignRole(UserRole::Staff->value);
        $privilegedStaff->givePermissionTo(['asset.viewAny', 'asset.create', 'asset.edit', 'asset.manage_finances']);

        $response = $this->actingAs($privilegedStaff)
            ->post(route('assets.store'), [
                'name'              => 'New Asset Privileged',
                'asset_category_id' => $this->category->id,
                'status'            => AssetStatus::Spare->value,
                'quantity'          => 1,
                'purchase_date'     => '2026-06-01',
                'mutation_date'     => '2026-06-15',
                'purchase_price'    => 3500000.00,
            ]);

        $response->assertRedirect();

        $asset = Asset::where('name', 'New Asset Privileged')->first();
        $this->assertNotNull($asset);
        $this->assertEquals('2026-06-15', $asset->mutation_date->format('Y-m-d'));
        $this->assertEquals('2026-06-01', $asset->purchase_date->format('Y-m-d'));
        $this->assertEquals(3500000.00, $asset->purchase_price);
    }

    /** @test */
    public function admin_can_update_asset_with_mutation_date_and_purchase_price(): void
    {
        $asset = Asset::create([
            'name'              => 'Asset To Update',
            'asset_category_id' => $this->category->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
            'purchase_price'    => 1000000.00,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('assets.update', $asset), [
                'name'              => 'Asset Updated By Admin',
                'asset_category_id' => $this->category->id,
                'status'            => AssetStatus::Spare->value,
                'quantity'          => 1,
                'mutation_date'     => '2026-06-20',
                'purchase_price'    => 1200000.00,
            ]);

        $response->assertRedirect();

        $asset->refresh();
        $this->assertEquals('Asset Updated By Admin', $asset->name);
        $this->assertEquals('2026-06-20', $asset->mutation_date->format('Y-m-d'));
        $this->assertEquals(1200000.00, $asset->purchase_price);
    }

    /** @test */
    public function staff_can_update_asset_with_mutation_date_but_purchase_price_is_ignored_and_retained(): void
    {
        $asset = Asset::create([
            'name'              => 'Asset To Update 2',
            'asset_category_id' => $this->category->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
            'purchase_price'    => 1000000.00,
        ]);

        $response = $this->actingAs($this->staffUser)
            ->put(route('assets.update', $asset), [
                'name'              => 'Asset Updated By Staff',
                'asset_category_id' => $this->category->id,
                'status'            => AssetStatus::Spare->value,
                'quantity'          => 1,
                'mutation_date'     => '2026-06-20',
                'purchase_price'    => 1200000.00, // Should be ignored
            ]);

        $response->assertRedirect();

        $asset->refresh();
        $this->assertEquals('Asset Updated By Staff', $asset->name);
        $this->assertEquals('2026-06-20', $asset->mutation_date->format('Y-m-d'));
        $this->assertEquals(1000000.00, $asset->purchase_price); // Retains original price
    }

    /** @test */
    public function staff_with_only_mutation_permission_can_mutate_asset_but_general_fields_are_ignored(): void
    {
        $mutationOnlyStaff = User::create([
            'name'     => 'Mutation Only Staff',
            'email'    => 'mutstaff@example.com',
            'password' => bcrypt('password'),
            'role'     => UserRole::Staff,
        ]);
        $mutationOnlyStaff->assignRole(UserRole::Staff->value);
        $mutationOnlyStaff->givePermissionTo(['asset.viewAny', 'asset.mutate']);

        $asset = Asset::create([
            'name'              => 'Original Asset Name',
            'asset_category_id' => $this->category->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
            'brand'             => 'Original Brand',
            'purchase_price'    => 500000.00,
        ]);

        $response = $this->actingAs($mutationOnlyStaff)
            ->put(route('assets.update', $asset), [
                'name'              => 'Hacker Name Change',
                'brand'             => 'Hacker Brand Change',
                'status'            => AssetStatus::InUse->value, // Allowed in mutation
                'mutation_date'     => '2026-06-25',             // Allowed in mutation
                'purchase_price'    => 10000.00,                 // Ignored
            ]);

        $response->assertRedirect();

        $asset->refresh();
        $this->assertEquals('Original Asset Name', $asset->name); // General fields preserved
        $this->assertEquals('Original Brand', $asset->brand);     // General fields preserved
        $this->assertEquals(AssetStatus::InUse, $asset->status);   // Mutation field changed
        $this->assertEquals('2026-06-25', $asset->mutation_date->format('Y-m-d')); // Mutation field changed
        $this->assertEquals(500000.00, $asset->purchase_price);    // Finance field preserved
    }

    /** @test */
    public function admin_can_see_purchase_price_on_details_page(): void
    {
        $asset = Asset::create([
            'name'              => 'Asset Details Admin',
            'asset_category_id' => $this->category->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
            'purchase_price'    => 5000000.00,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('assets.show', $asset));

        $response->assertStatus(200);
        $response->assertSee('Harga Pembelian');
        $response->assertSee('Rp 5.000.000');
    }

    /** @test */
    public function staff_cannot_see_purchase_price_on_details_page_without_finances_permission(): void
    {
        $asset = Asset::create([
            'name'              => 'Asset Details Staff',
            'asset_category_id' => $this->category->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
            'purchase_price'    => 5000000.00,
        ]);

        $response = $this->actingAs($this->staffUser)
            ->get(route('assets.show', $asset));

        $response->assertStatus(200);
        $response->assertDontSee('Harga Pembelian');
        $response->assertDontSee('Rp 5.000.000');
    }

    /** @test */
    public function staff_with_finances_permission_can_see_purchase_price_on_details_page(): void
    {
        $privilegedStaff = User::create([
            'name'     => 'Privileged Staff',
            'email'    => 'privstaff@example.com',
            'password' => bcrypt('password'),
            'role'     => UserRole::Staff,
        ]);
        $privilegedStaff->assignRole(UserRole::Staff->value);
        $privilegedStaff->givePermissionTo(['asset.viewAny', 'asset.manage_finances']);

        $asset = Asset::create([
            'name'              => 'Asset Details Privileged',
            'asset_category_id' => $this->category->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
            'purchase_price'    => 5000000.00,
        ]);

        $response = $this->actingAs($privilegedStaff)
            ->get(route('assets.show', $asset));

        $response->assertStatus(200);
        $response->assertSee('Harga Pembelian');
        $response->assertSee('Rp 5.000.000');
    }
}
