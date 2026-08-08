<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorControllerTest extends TestCase
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
            'username' => 'admin-vendor',
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);
    }

    /** @test */
    public function admin_can_view_vendors_index()
    {
        Vendor::create(['name' => 'PT Supplier']);

        $response = $this->actingAs($this->admin)->get(route('admin.vendors.index'));

        $response->assertStatus(200);
        $response->assertSee('PT Supplier');
    }

    /** @test */
    public function admin_can_view_create_vendor_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.vendors.create'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_store_vendor()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.vendors.store'), [
            'name'  => 'PT Teknologi',
            'phone' => '021-123456',
            'email' => 'info@ptteknologi.com',
        ]);

        $response->assertRedirect(route('admin.vendors.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('vendors', ['name' => 'PT Teknologi']);
    }

    /** @test */
    public function store_validates_unique_name()
    {
        Vendor::create(['name' => 'PT Existing']);

        $response = $this->actingAs($this->admin)->post(route('admin.vendors.store'), [
            'name' => 'PT Existing',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function admin_can_view_edit_vendor_page()
    {
        $vendor = Vendor::create(['name' => 'PT Vendor']);

        $response = $this->actingAs($this->admin)->get(route('admin.vendors.edit', $vendor));

        $response->assertStatus(200);
        $response->assertSee('PT Vendor');
    }

    /** @test */
    public function admin_can_update_vendor()
    {
        $vendor = Vendor::create(['name' => 'PT Old']);

        $response = $this->actingAs($this->admin)->put(route('admin.vendors.update', $vendor), [
            'name' => 'PT Updated',
        ]);

        $response->assertRedirect(route('admin.vendors.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('vendors', ['name' => 'PT Updated']);
    }

    /** @test */
    public function admin_can_search_vendors()
    {
        Vendor::create(['name' => 'PT Alpha']);
        Vendor::create(['name' => 'PT Beta']);

        $response = $this->actingAs($this->admin)->get(route('admin.vendors.index', ['search' => 'Alpha']));

        $response->assertStatus(200);
        $response->assertSee('PT Alpha');
        $response->assertDontSee('PT Beta');
    }

    /** @test */
    public function admin_can_get_create_vendor_form_via_ajax()
    {
        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->get(route('admin.vendors.create'));

        $response->assertStatus(200);
        $response->assertSee('vendorCreateForm', false);
    }

    /** @test */
    public function admin_can_store_vendor_via_ajax_json_request()
    {
        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.vendors.store'), ['name' => 'PT Ajax']);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('vendors', ['name' => 'PT Ajax']);
    }

    /** @test */
    public function ajax_vendor_store_returns_validation_errors_as_json()
    {
        Vendor::create(['name' => 'PT Existing']);

        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.vendors.store'), ['name' => 'PT Existing']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function admin_can_delete_vendor()
    {
        $vendor = Vendor::create(['name' => 'PT Delete']);

        $response = $this->actingAs($this->admin)->delete(route('admin.vendors.destroy', $vendor));

        $response->assertRedirect(route('admin.vendors.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('vendors', ['name' => 'PT Delete']);
    }
}
