<?php

namespace App\Http\Controllers;

use App\Http\Requests\IssuePeripheralRequest;
use App\Http\Requests\StorePeripheralRequest;
use App\Http\Requests\UpdatePeripheralRequest;
use App\Models\Brand;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Peripheral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PeripheralController extends Controller
{
    public function index(): View
    {
        $this->authorize('peripheral.viewAny');

        $peripherals = Peripheral::with(['brand', 'location'])
            ->orderBy('name')
            ->paginate(15);

        $employees = Employee::active()->orderBy('name')->get(['id', 'name', 'department']);
        $locations = Location::orderBy('name')->get(['id', 'name']);

        return view('admin.peripherals.index', compact('peripherals', 'employees', 'locations'));
    }

    public function create(): View
    {
        $this->authorize('peripheral.create');

        $brands    = Brand::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('admin.peripherals.create', compact('brands', 'locations'));
    }

    public function store(StorePeripheralRequest $request): RedirectResponse
    {
        $this->authorize('peripheral.create');

        DB::beginTransaction();
        try {
            $data = $request->validated();

            $peripheral = Peripheral::create($data);
            $peripheral->current_stock = $data['total_stock'];
            $peripheral->save();

            DB::commit();

            return redirect()
                ->route('admin.peripherals.index')
                ->with('success', 'Peripheral berhasil ditambahkan.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan peripheral.', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Gagal menyimpan peripheral. Silakan coba lagi.');
        }
    }

    public function show(Peripheral $peripheral): View
    {
        $this->authorize('peripheral.viewAny');

        $peripheral->load(['brand', 'location', 'issuances.employee', 'issuances.issuedBy']);

        $employees = Employee::active()->orderBy('name')->get(['id', 'name', 'department']);
        $locations = Location::orderBy('name')->get(['id', 'name']);

        return view('admin.peripherals.show', compact('peripheral', 'employees', 'locations'));
    }

    public function edit(Peripheral $peripheral): View
    {
        $this->authorize('peripheral.edit');

        $brands    = Brand::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('admin.peripherals.edit', compact('peripheral', 'brands', 'locations'));
    }

    public function update(UpdatePeripheralRequest $request, Peripheral $peripheral): RedirectResponse
    {
        $this->authorize('peripheral.edit');

        DB::beginTransaction();
        try {
            $peripheral->update($request->validated());
            DB::commit();

            return redirect()
                ->route('admin.peripherals.index')
                ->with('success', "Peripheral {$peripheral->name} berhasil diperbarui.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal update peripheral ID: {$peripheral->id}.", ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Gagal memperbarui peripheral. Silakan coba lagi.');
        }
    }

    public function destroy(Peripheral $peripheral): RedirectResponse
    {
        $this->authorize('peripheral.delete');

        DB::beginTransaction();
        try {
            $name = $peripheral->name;
            $peripheral->delete();
            DB::commit();

            return redirect()
                ->route('admin.peripherals.index')
                ->with('success', "Peripheral {$name} berhasil dihapus.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal hapus peripheral ID: {$peripheral->id}.", ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus peripheral. Silakan coba lagi.');
        }
    }

    public function issue(IssuePeripheralRequest $request, Peripheral $peripheral): RedirectResponse
    {
        $this->authorize('peripheral.issue');

        $quantity = $request->integer('quantity');

        DB::beginTransaction();
        try {
            $peripheral = Peripheral::lockForUpdate()->findOrFail($peripheral->id);

            if ($quantity > $peripheral->current_stock) {
                DB::rollBack();
                return back()->with('error', "Stok tidak mencukupi. Stok saat ini: {$peripheral->current_stock}.");
            }

            $peripheral->decrement('current_stock', $quantity);

            $peripheral->issuances()->create([
                'employee_id' => $request->input('employee_id'),
                'issued_by'   => auth()->id(),
                'location_id' => $request->input('location_id'),
                'quantity'    => $quantity,
                'notes'       => $request->input('notes'),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.peripherals.show', $peripheral)
                ->with('success', "{$quantity} {$peripheral->name} berhasil diambil.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal issue peripheral ID: {$peripheral->id}.", ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal memproses pengambilan. Silakan coba lagi.');
        }
    }

    public function restock(Request $request, Peripheral $peripheral): RedirectResponse
    {
        $this->authorize('peripheral.edit');

        $validated = $request->validate([
            'quantity'    => ['required', 'integer', 'min:1', 'max:9999'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ]);

        DB::beginTransaction();
        try {
            $peripheral = Peripheral::lockForUpdate()->findOrFail($peripheral->id);

            $peripheral->increment('total_stock', $validated['quantity']);
            $peripheral->increment('current_stock', $validated['quantity']);

            $peripheral->issuances()->create([
                'employee_id' => $validated['employee_id'],
                'issued_by'   => auth()->id(),
                'location_id' => $validated['location_id'],
                'quantity'    => $validated['quantity'],
                'notes'       => 'Restok: ' . ($validated['notes'] ?? ''),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.peripherals.show', $peripheral)
                ->with('success', "Stok {$peripheral->name} berhasil ditambah {$validated['quantity']}.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal restok peripheral ID: {$peripheral->id}.", ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menambah stok. Silakan coba lagi.');
        }
    }
}
