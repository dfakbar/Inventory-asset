<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationControllerTest extends TestCase
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
            'username' => 'admin-location',
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);
    }

    /** @test */
    public function admin_can_view_locations_index()
    {
        Location::create(['name' => 'Ruang Server']);

        $response = $this->actingAs($this->admin)->get(route('admin.locations.index'));

        $response->assertStatus(200);
        $response->assertSee('Ruang Server');
    }

    /** @test */
    public function admin_can_view_create_location_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.locations.create'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_store_location()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.locations.store'), [
            'name' => 'Gudang IT',
        ]);

        $response->assertRedirect(route('admin.locations.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('locations', ['name' => 'Gudang IT']);
    }

    /** @test */
    public function store_validates_unique_name()
    {
        Location::create(['name' => 'Gudang IT']);

        $response = $this->actingAs($this->admin)->post(route('admin.locations.store'), [
            'name' => 'Gudang IT',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function admin_can_view_edit_location_page()
    {
        $location = Location::create(['name' => 'Ruang Meeting']);

        $response = $this->actingAs($this->admin)->get(route('admin.locations.edit', $location));

        $response->assertStatus(200);
        $response->assertSee('Ruang Meeting');
    }

    /** @test */
    public function admin_can_update_location()
    {
        $location = Location::create(['name' => 'Old Location']);

        $response = $this->actingAs($this->admin)->put(route('admin.locations.update', $location), [
            'name' => 'Updated Location',
        ]);

        $response->assertRedirect(route('admin.locations.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('locations', ['name' => 'Updated Location']);
    }

    /** @test */
    public function update_validates_unique_name_ignoring_self()
    {
        $locA = Location::create(['name' => 'Location A']);
        Location::create(['name' => 'Location B']);

        $response = $this->actingAs($this->admin)->put(route('admin.locations.update', $locA), [
            'name' => 'Location B',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function admin_can_delete_location()
    {
        $location = Location::create(['name' => 'Delete Me']);

        $response = $this->actingAs($this->admin)->delete(route('admin.locations.destroy', $location));

        $response->assertRedirect(route('admin.locations.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('locations', ['name' => 'Delete Me']);
    }

    /** @test */
    public function admin_cannot_delete_location_with_assets()
    {
        $location = Location::create(['name' => 'Used Location']);
        Asset::factory()->create(['location_id' => $location->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.locations.destroy', $location));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('locations', ['name' => 'Used Location']);
    }

    /** @test */
    public function location_auto_generates_slug_on_create()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.locations.store'), [
            'name' => 'Ruang Server',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('locations', [
            'name' => 'Ruang Server',
            'slug' => 'ruang-server',
        ]);
    }

    /** @test */
    public function location_slug_is_unique()
    {
        Location::create(['name' => 'Server Room', 'slug' => 'ruang-server']);

        $response = $this->actingAs($this->admin)->post(route('admin.locations.store'), [
            'name' => 'Ruang Server',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('locations', [
            'name' => 'Ruang Server',
            'slug' => 'ruang-server-1',
        ]);
    }

    /** @test */
    public function location_slug_regenerates_on_name_change()
    {
        $location = Location::create(['name' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAs($this->admin)->put(route('admin.locations.update', $location), [
            'name' => 'New Name',
        ]);

        $location->refresh();
        $this->assertEquals('new-name', $location->slug);
    }

    /** @test */
    public function location_preserves_manual_slug()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.locations.store'), [
            'name' => 'Custom Location',
            'slug' => 'custom-slug',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('locations', [
            'name' => 'Custom Location',
            'slug' => 'custom-slug',
        ]);
    }
}
