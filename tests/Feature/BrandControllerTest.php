<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\Brand;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandControllerTest extends TestCase
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
            'username' => 'admin-brand',
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);
    }

    /** @test */
    public function admin_can_view_brands_index()
    {
        Brand::create(['name' => 'Dell']);

        $response = $this->actingAs($this->admin)->get(route('admin.brands.index'));

        $response->assertStatus(200);
        $response->assertSee('Dell');
    }

    /** @test */
    public function admin_can_view_create_brand_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.brands.create'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_store_brand()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.brands.store'), [
            'name' => 'HP',
        ]);

        $response->assertRedirect(route('admin.brands.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('brands', ['name' => 'HP']);
    }

    /** @test */
    public function store_validates_unique_name()
    {
        Brand::create(['name' => 'Dell']);

        $response = $this->actingAs($this->admin)->post(route('admin.brands.store'), [
            'name' => 'Dell',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function admin_can_view_edit_brand_page()
    {
        $brand = Brand::create(['name' => 'Lenovo']);

        $response = $this->actingAs($this->admin)->get(route('admin.brands.edit', $brand));

        $response->assertStatus(200);
        $response->assertSee('Lenovo');
    }

    /** @test */
    public function admin_can_update_brand()
    {
        $brand = Brand::create(['name' => 'Old Brand']);

        $response = $this->actingAs($this->admin)->put(route('admin.brands.update', $brand), [
            'name' => 'Updated Brand',
        ]);

        $response->assertRedirect(route('admin.brands.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('brands', ['name' => 'Updated Brand']);
    }

    /** @test */
    public function admin_can_delete_brand()
    {
        $brand = Brand::create(['name' => 'Delete Me']);

        $response = $this->actingAs($this->admin)->delete(route('admin.brands.destroy', $brand));

        $response->assertRedirect(route('admin.brands.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('brands', ['name' => 'Delete Me']);
    }

    /** @test */
    public function admin_cannot_delete_brand_with_assets()
    {
        $brand = Brand::create(['name' => 'Used Brand']);
        Asset::factory()->create(['brand_id' => $brand->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.brands.destroy', $brand));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('brands', ['name' => 'Used Brand']);
    }
}
