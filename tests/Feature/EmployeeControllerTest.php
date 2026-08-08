<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
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
            'username' => 'admin-employee',
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);
    }

    /** @test */
    public function admin_can_view_employees_index()
    {
        Employee::create(['name' => 'Budi Santoso']);

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index'));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
    }

    /** @test */
    public function admin_can_search_employees()
    {
        Employee::create(['name' => 'Budi Santoso', 'department' => 'IT']);
        Employee::create(['name' => 'Siti Aminah', 'department' => 'HRGA']);

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index', ['search' => 'Budi']));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Siti Aminah');
    }

    /** @test */
    public function admin_can_get_create_employee_form_via_ajax()
    {
        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->get(route('admin.employees.create'));

        $response->assertStatus(200);
        $response->assertSee('employeeCreateForm', false);
    }

    /** @test */
    public function admin_can_store_employee()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.employees.store'), [
            'name'       => 'Bambang Suprapto',
            'department' => 'IT',
        ]);

        $response->assertRedirect(route('admin.employees.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('employees', ['name' => 'Bambang Suprapto']);
    }

    /** @test */
    public function admin_can_store_employee_via_ajax_json_request()
    {
        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.employees.store'), [
                'name'       => 'Ajax Employee',
                'department' => 'IT',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('employees', ['name' => 'Ajax Employee']);
    }

    /** @test */
    public function ajax_employee_store_returns_validation_errors_as_json()
    {
        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.employees.store'), [
                'name' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }
}
