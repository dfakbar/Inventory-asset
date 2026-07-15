<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
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
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);
    }

    /** @test */
    public function admin_can_view_categories_index()
    {
        AssetCategory::factory()->create(['name' => 'Monitor']);

        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Monitor');
    }

    /** @test */
    public function admin_can_view_create_category_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.categories.create'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_store_category()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name'         => 'New Category',
            'abbreviation' => 'NCT',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('asset_categories', ['name' => 'New Category', 'abbreviation' => 'NCT']);
    }

    /** @test */
    public function store_validates_unique_name()
    {
        AssetCategory::create(['name' => 'Existing', 'abbreviation' => 'EXT']);

        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name'         => 'Existing',
            'abbreviation' => 'NEW',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function store_validates_unique_abbreviation()
    {
        AssetCategory::create(['name' => 'Existing', 'abbreviation' => 'EXT']);

        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name'         => 'New Category',
            'abbreviation' => 'EXT',
        ]);

        $response->assertSessionHasErrors('abbreviation');
    }

    /** @test */
    public function admin_can_view_edit_category_page()
    {
        $category = AssetCategory::create(['name' => 'Monitor', 'abbreviation' => 'MON']);

        $response = $this->actingAs($this->admin)->get(route('admin.categories.edit', $category));

        $response->assertStatus(200);
        $response->assertSee('Monitor');
    }

    /** @test */
    public function admin_can_update_category()
    {
        $category = AssetCategory::create(['name' => 'Old Name', 'abbreviation' => 'OLD']);

        $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name'         => 'Updated Name',
            'abbreviation' => 'UPD',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('asset_categories', ['name' => 'Updated Name']);
    }

    /** @test */
    public function update_validates_unique_name_ignoring_self()
    {
        $category = AssetCategory::create(['name' => 'Category A', 'abbreviation' => 'CTA']);
        AssetCategory::create(['name' => 'Category B', 'abbreviation' => 'CTB']);

        $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name'         => 'Category B',
            'abbreviation' => 'CTA',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function admin_can_delete_category()
    {
        $category = AssetCategory::create(['name' => 'Delete Me', 'abbreviation' => 'DEL']);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('asset_categories', ['name' => 'Delete Me']);
    }

    /** @test */
    public function admin_cannot_delete_category_with_assets()
    {
        $category = AssetCategory::create(['name' => 'Used Cat', 'abbreviation' => 'USD']);
        Asset::factory()->create(['asset_category_id' => $category->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('asset_categories', ['name' => 'Used Cat']);
    }
}
