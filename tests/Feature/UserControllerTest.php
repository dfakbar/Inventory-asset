<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::create([
            'name'     => 'Admin',
            'username' => 'admin',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);
    }

    /** @test */
    public function admin_can_view_users_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Admin');
    }

    /** @test */
    public function admin_can_view_create_user_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.create'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_search_users_by_name_username_or_email()
    {
        User::create([
            'name'     => 'Budi Santoso',
            'username' => 'budisan',
            'email'    => 'budi@test.com',
            'password' => bcrypt('password'),
            'role'     => UserRole::Staff,
        ]);
        User::create([
            'name'     => 'Siti Aminah',
            'username' => 'sitiaminah',
            'email'    => 'siti@test.com',
            'password' => bcrypt('password'),
            'role'     => UserRole::Staff,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'budi']));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Siti Aminah');
    }

    /** @test */
    public function admin_can_store_user_via_ajax_json_request()
    {
        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.users.store'), [
                'name'                  => 'Ajax User',
                'username'              => 'ajaxuser',
                'email'                 => 'ajax@test.com',
                'password'              => 'password1',
                'password_confirmation' => 'password1',
                'role'                  => UserRole::Staff->value,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('users', ['name' => 'Ajax User', 'email' => 'ajax@test.com']);
    }

    /** @test */
    public function ajax_store_returns_validation_errors_as_json()
    {
        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.users.store'), [
                'name'                  => 'Admin',
                'username'              => 'admin',
                'email'                 => 'admin@test.com',
                'password'              => 'password1',
                'password_confirmation' => 'password1',
                'role'                  => UserRole::Admin->value,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['username', 'email']);
    }

    /** @test */
    public function admin_can_get_create_form_partial_via_ajax()
    {
        $response = $this->actingAs($this->admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertSee('userCreateForm', false);
    }

    /** @test */
    public function admin_can_store_admin_user()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name'                  => 'New Admin',
            'username'              => 'newadmin',
            'email'                 => 'newadmin@test.com',
            'password'              => 'password1',
            'password_confirmation' => 'password1',
            'role'                  => UserRole::Admin->value,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['name' => 'New Admin', 'role' => UserRole::Admin->value]);
    }

    /** @test */
    public function admin_can_store_staff_user_with_permissions()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name'                  => 'New Staff',
            'username'              => 'newstaff',
            'email'                 => 'staff@test.com',
            'password'              => 'password1',
            'password_confirmation' => 'password1',
            'role'                  => UserRole::Staff->value,
            'permissions'           => ['asset.viewAny', 'asset.create'],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $user = User::where('email', 'staff@test.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasPermissionTo('asset.viewAny'));
        $this->assertTrue($user->hasPermissionTo('asset.create'));
    }

    /** @test */
    public function admin_can_view_edit_user_page()
    {
        $user = User::create([
            'name'     => 'Edit Me',
            'username' => 'editme',
            'email'    => 'editme@test.com',
            'password' => bcrypt('password'),
            'role'     => UserRole::Staff,
        ]);
        $user->assignRole(UserRole::Staff->value);

        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $user));

        $response->assertStatus(200);
        $response->assertSee('Edit Me');
    }

    /** @test */
    public function admin_can_update_user()
    {
        $user = User::create([
            'name'     => 'Old Name',
            'username' => 'oldname',
            'email'    => 'old@test.com',
            'password' => bcrypt('password'),
            'role'     => UserRole::Staff,
        ]);
        $user->assignRole(UserRole::Staff->value);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
            'name'     => 'Updated Name',
            'username' => 'oldname',
            'email'    => 'old@test.com',
            'role'     => UserRole::Staff->value,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['name' => 'Updated Name']);
    }

    /** @test */
    public function admin_cannot_downgrade_own_role()
    {
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin), [
            'name'     => 'Admin',
            'username' => 'admin',
            'email'    => 'admin@test.com',
            'role'     => UserRole::Staff->value,
        ]);

        $response->assertSessionHas('error');
        $this->admin->refresh();
        $this->assertEquals(UserRole::Admin, $this->admin->role);
    }

    /** @test */
    public function admin_can_delete_user()
    {
        $user = User::create([
            'name'     => 'Delete Me',
            'username' => 'deleteme',
            'email'    => 'delete@test.com',
            'password' => bcrypt('password'),
            'role'     => UserRole::Staff,
        ]);
        $user->assignRole(UserRole::Staff->value);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('users', ['email' => 'delete@test.com']);
    }

    /** @test */
    public function admin_cannot_delete_self()
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['email' => 'admin@test.com']);
    }
}
