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
    public function admin_can_search_brands()
    {
        Brand::create(['name' => 'Dell']);
        Brand::create(['name' => 'HP']);

        $response = $this->actingAs($this->admin)->get(route('admin.brands.index', ['search' => 'Dell']));

        $response->assertStatus(200);
        $response->assertSee('Dell');
        $response->assertDontSee('HP');
    }

    /** @test */
    public function admin_can_get_create_brand_form_via_ajax()
    {
        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->get(route('admin.brands.create'));

        $response->assertStatus(200);
        $response->assertSee('brandCreateForm', false);
    }

    /** @test */
    public function admin_can_store_brand_via_ajax_json_request()
    {
        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.brands.store'), ['name' => 'Ajax Brand']);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('brands', ['name' => 'Ajax Brand']);
    }

    /** @test */
    public function ajax_brand_store_returns_validation_errors_as_json()
    {
        Brand::create(['name' => 'Dell']);

        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.brands.store'), ['name' => 'Dell']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
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
