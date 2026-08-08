<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\Peripheral;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeripheralControllerTest extends TestCase
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
            'username' => 'admin-peripheral',
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);
    }

    private function storePeripheral(array $overrides = []): array
    {
        return array_merge([
            'name'        => 'SSD Samsung 870 EVO 500GB',
            'brand_id'    => null,
            'model'       => '870 EVO',
            'location_id' => null,
            'total_stock' => 10,
            'notes'       => null,
        ], $overrides);
    }

    /** @test */
    public function admin_can_view_peripherals_index()
    {
        Peripheral::create($this->storePeripheral(['name' => 'Keyboard Mechanical']));

        $response = $this->actingAs($this->admin)->get(route('admin.peripherals.index'));

        $response->assertStatus(200);
        $response->assertSee('Keyboard Mechanical');
    }

    /** @test */
    public function admin_can_search_peripherals_by_name()
    {
        Peripheral::create($this->storePeripheral(['name' => 'Mouse Logitech']));
        Peripheral::create($this->storePeripheral(['name' => 'Keyboard Logitech']));

        $response = $this->actingAs($this->admin)->get(route('admin.peripherals.index', ['search' => 'Mouse']));

        $response->assertStatus(200);
        $response->assertSee('Mouse Logitech');
        $response->assertDontSee('Keyboard Logitech');
    }

    /** @test */
    public function admin_can_search_peripherals_by_brand()
    {
        $brand = Brand::create(['name' => 'Logitech']);
        Peripheral::create($this->storePeripheral(['name' => 'Mouse', 'brand_id' => $brand->id]));
        Peripheral::create($this->storePeripheral(['name' => 'Keyboard']));

        $response = $this->actingAs($this->admin)->get(route('admin.peripherals.index', ['search' => 'Logitech']));

        $response->assertStatus(200);
        $response->assertSee('Mouse');
        $response->assertDontSee('Keyboard');
    }

    /** @test */
    public function admin_can_get_create_peripheral_form_via_ajax()
    {
        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->get(route('admin.peripherals.create'));

        $response->assertStatus(200);
        $response->assertSee('peripheralCreateForm', false);
    }

    /** @test */
    public function admin_can_store_peripheral()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.peripherals.store'), $this->storePeripheral());

        $response->assertRedirect(route('admin.peripherals.index'));
        $response->assertSessionHas('success');

        $peripheral = Peripheral::where('name', 'SSD Samsung 870 EVO 500GB')->first();
        $this->assertNotNull($peripheral);
        $this->assertEquals(10, $peripheral->total_stock);
        $this->assertEquals(10, $peripheral->current_stock);
    }

    /** @test */
    public function admin_can_store_peripheral_via_ajax_json_request()
    {
        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.peripherals.store'), $this->storePeripheral(['name' => 'Ajax Peripheral']));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('peripherals', ['name' => 'Ajax Peripheral']);
    }

    /** @test */
    public function ajax_peripheral_store_returns_validation_errors_as_json()
    {
        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.peripherals.store'), [
                'name' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'total_stock']);
    }
}
