<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $this->authorize('employee.viewAny');

        $employees = Employee::withCount('assets')->orderBy('name')->paginate(15);

        return view('admin.employees.index', compact('employees'));
    }

    public function create(): View
    {
        $this->authorize('employee.create');

        return view('admin.employees.create');
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $this->authorize('employee.create');

        DB::beginTransaction();
        try {
            $employee = Employee::create($request->validated());
            DB::commit();

            return redirect()
                ->route('admin.employees.index')
                ->with('success', "Pengguna {$employee->name} berhasil ditambahkan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membuat pengguna.', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Gagal menyimpan pengguna. Silakan coba lagi.');
        }
    }

    public function show(Employee $employee): RedirectResponse
    {
        return redirect()->route('admin.employees.edit', $employee);
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('employee.edit');

        return view('admin.employees.edit', compact('employee'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->authorize('employee.edit');

        DB::beginTransaction();
        try {
            $employee->update($request->validated());
            DB::commit();

            return redirect()
                ->route('admin.employees.index')
                ->with('success', "Pengguna {$employee->name} berhasil diperbarui.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal update pengguna ID: {$employee->id}.", ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Gagal memperbarui pengguna. Silakan coba lagi.');
        }
    }

    public function toggleActive(Employee $employee): RedirectResponse
    {
        $this->authorize('employee.edit');

        $employee->update(['is_active' => ! $employee->is_active]);

        $status = $employee->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()
            ->route('admin.employees.index')
            ->with('success', "Pengguna {$employee->name} berhasil {$status}.");
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('employee.delete');

        if ($employee->assets()->exists()) {
            return back()->with(
                'error',
                "Pengguna {$employee->name} tidak dapat dihapus karena masih digunakan oleh {$employee->assets()->count()} aset."
            );
        }

        DB::beginTransaction();
        try {
            $name = $employee->name;
            $employee->delete();
            DB::commit();

            return redirect()
                ->route('admin.employees.index')
                ->with('success', "Pengguna {$name} berhasil dihapus.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal hapus pengguna ID: {$employee->id}.", ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus pengguna. Silakan coba lagi.');
        }
    }
}
