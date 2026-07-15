<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    // =========================================================
    // INDEX
    // =========================================================

    public function index(): View
    {
        $users = User::orderBy('name')->paginate(15);

        $roles = UserRole::cases();

        return view('admin.users.index', compact('users', 'roles'));
    }

    // =========================================================
    // CREATE
    // =========================================================

    public function create(): View
    {
        $roles            = UserRole::cases();
        $permissionGroups = PermissionSeeder::GROUPS;

        return view('admin.users.create', compact('roles', 'permissionGroups'));
    }

    // =========================================================
    // STORE
    // =========================================================

    public function store(StoreUserRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $request->validated('name'),
                'email'    => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
                'role'     => $request->validated('role'),
            ]);

            // Assign Spatie role
            $user->assignRole($request->validated('role'));

            if ($request->validated('role') === UserRole::Admin->value) {
                // Admin mendapat SEMUA permission via role (sudah di-set di PermissionSeeder)
                $user->syncPermissions([]);
            } else {
                // Staff mendapat hanya permission yang dipilih admin
                $user->syncPermissions($request->validated('permissions', []));
            }

            DB::commit();
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            return redirect()
                ->route('admin.users.index')
                ->with('success', "User {$user->name} berhasil ditambahkan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membuat user baru.', ['error' => $e->getMessage()]);

            return back()->withInput()
                ->with('error', 'Gagal menyimpan user. Silakan coba lagi.');
        }
    }

    // =========================================================
    // SHOW → redirect ke edit
    // =========================================================

    public function show(User $user): RedirectResponse
    {
        return redirect()->route('admin.users.edit', $user);
    }

    // =========================================================
    // EDIT
    // =========================================================

    public function edit(User $user): View
    {
        $roles              = UserRole::cases();
        $permissionGroups   = PermissionSeeder::GROUPS;
        $userPermissions    = $user->getDirectPermissions()->pluck('name')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'permissionGroups', 'userPermissions'));
    }

    // =========================================================
    // UPDATE
    // =========================================================

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        // Guard: admin tidak bisa downgrade role-nya sendiri
        if ($user->id === auth()->id() && $request->validated('role') !== UserRole::Admin->value) {
            return back()->withInput()
                ->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        DB::beginTransaction();
        try {
            $data = $request->safe()->except(['password', 'password_confirmation', 'permissions']);

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->validated('password'));
            }

            $user->update($data);

            // Sync Spatie role
            $user->syncRoles([$request->validated('role')]);

            if ($request->validated('role') === UserRole::Admin->value) {
                // Admin: hapus direct permissions (akses lewat role)
                $user->syncPermissions([]);
            } else {
                // Staff: sync direct permissions yang dipilih
                $user->syncPermissions($request->validated('permissions', []));
            }

            DB::commit();
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            return redirect()
                ->route('admin.users.index')
                ->with('success', "User {$user->name} berhasil diperbarui.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal update user ID: {$user->id}.", ['error' => $e->getMessage()]);

            return back()->withInput()
                ->with('error', 'Gagal memperbarui user. Silakan coba lagi.');
        }
    }

    // =========================================================
    // DESTROY
    // =========================================================

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        DB::beginTransaction();
        try {
            $userName = $user->name;
            $user->delete();

            DB::commit();
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            return redirect()
                ->route('admin.users.index')
                ->with('success', "User {$userName} berhasil dihapus.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal menghapus user ID: {$user->id}.", ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus user. Silakan coba lagi.');
        }
    }
}
