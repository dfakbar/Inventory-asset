<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMutationLog;
use App\Models\Brand;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Peripheral;
use App\Models\SopDocument;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SopDocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Asset $asset;
    private Employee $employee;
    private Location $location;
    private AssetMutationLog $mutationLog;
    private Peripheral $peripheral;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'username' => 'admin-sop',
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);

        $this->staff = User::create([
            'name'     => 'Staff',
            'email'    => 'staff@test.com',
            'password' => bcrypt('password'),
            'username' => 'staff-sop',
            'role'     => UserRole::Staff->value,
        ]);
        $this->staff->assignRole(UserRole::Staff->value);

        $category = AssetCategory::create(['name' => 'Laptop', 'abbreviation' => 'LPT']);
        Brand::create(['name' => 'Dell']);
        $this->location = Location::create(['name' => 'Ruang IT']);
        $this->employee = Employee::create([
            'name'    => 'Budi Karyawan',
            'is_active' => true,
        ]);

        $this->asset = Asset::create([
            'name'              => 'Laptop Uji',
            'asset_category_id' => $category->id,
            'location_id'       => $this->location->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
            'assigned_to'       => $this->admin->id,
        ]);

        $this->mutationLog = AssetMutationLog::create([
            'asset_id'        => $this->asset->id,
            'performed_by'    => $this->admin->id,
            'from_status'     => AssetStatus::Spare->value,
            'to_status'       => AssetStatus::InUse->value,
            'mutation_date'   => now(),
        ]);

        $this->peripheral = Peripheral::create([
            'name'        => 'Mouse Wireless',
            'brand_id'    => Brand::first()->id,
            'total_stock' => 5,
        ]);
    }

    /** @test */
    public function admin_can_view_documents_index()
    {
        $response = $this->actingAs($this->admin)->get(route('documents.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function staff_without_permission_cannot_access_documents()
    {
        $response = $this->actingAs($this->staff)->get(route('documents.index'));

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_create_registration_document()
    {
        $response = $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type' => 'registrasi',
            'asset_ids'     => [$this->asset->id],
            'document_date' => '2026-08-04',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sop_documents', [
            'document_type'  => 'registrasi',
            'asset_id'       => $this->asset->id,
        ]);

        $doc = SopDocument::where('document_type', 'registrasi')->first();
        $this->assertStringStartsWith('FRA-2026-', $doc->document_number);
        $this->assertNotNull($doc->pdf_path);
    }

    /** @test */
    public function document_numbers_increment_per_type()
    {
        $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type' => 'registrasi',
            'asset_ids'     => [$this->asset->id],
        ]);
        $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type' => 'registrasi',
            'asset_ids'     => [$this->asset->id],
        ]);

        $first = SopDocument::where('document_type', 'registrasi')->orderBy('id')->first();
        $last = SopDocument::where('document_type', 'registrasi')->orderByDesc('id')->first();

        $this->assertStringEndsWith('-0001', $first->document_number);
        $this->assertStringEndsWith('-0002', $last->document_number);
    }

    /** @test */
    public function admin_can_create_receipt_document()
    {
        $response = $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type'        => 'tanda_terima',
            'asset_ids'            => [$this->asset->id],
            'recipient_employee_id'=> $this->employee->id,
            'data'                 => ['giver_name' => 'Giver', 'purpose' => 'Kantor'],
            'document_date'        => '2026-08-04',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('sop_documents', [
            'document_type'         => 'tanda_terima',
            'asset_id'              => $this->asset->id,
            'recipient_employee_id' => $this->employee->id,
        ]);
    }

    /** @test */
    public function admin_can_create_receipt_document_with_peripheral_only()
    {
        $response = $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type'        => 'tanda_terima',
            'peripheral_ids'       => [$this->peripheral->id],
            'recipient_employee_id'=> $this->employee->id,
            'data'                 => ['giver_name' => 'Giver', 'purpose' => 'Kantor'],
            'document_date'        => '2026-08-04',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $doc = SopDocument::where('document_type', 'tanda_terima')->latest()->first();
        $this->assertNotNull($doc);
        $this->assertNull($doc->asset_id);
        $this->assertEquals([$this->peripheral->id], $doc->data['peripheral_ids']);
        $this->assertNotNull($doc->pdf_path);
    }

    /** @test */
    public function admin_can_create_receipt_document_with_assets_and_peripherals()
    {
        $response = $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type'        => 'tanda_terima',
            'asset_ids'            => [$this->asset->id],
            'peripheral_ids'       => [$this->peripheral->id],
            'recipient_employee_id'=> $this->employee->id,
            'data'                 => ['giver_name' => 'Giver', 'purpose' => 'Kantor'],
        ]);

        $response->assertRedirect();

        $doc = SopDocument::where('document_type', 'tanda_terima')->latest()->first();
        $this->assertEquals([$this->asset->id], $doc->data['asset_ids']);
        $this->assertEquals([$this->peripheral->id], $doc->data['peripheral_ids']);
    }

    /** @test */
    public function receipt_document_requires_at_least_one_asset_or_peripheral()
    {
        $response = $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type'        => 'tanda_terima',
            'asset_ids'            => [],
            'peripheral_ids'       => [],
            'recipient_employee_id'=> $this->employee->id,
        ]);

        $response->assertSessionHasErrors('asset_ids');
    }

    /** @test */
    public function receipt_with_empty_asset_row_and_peripheral_only_is_valid()
    {
        // Meniru submit dari UI: baris Aset dibiarkan kosong (''), hanya Peripheral diisi.
        $response = $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type'        => 'tanda_terima',
            'asset_ids'            => [''],
            'peripheral_ids'       => [$this->peripheral->id],
            'recipient_employee_id'=> $this->employee->id,
            'data'                 => ['giver_name' => 'Giver', 'purpose' => 'Kantor'],
            'document_date'        => '2026-08-04',
        ]);

        $response->assertRedirect();

        $doc = SopDocument::where('document_type', 'tanda_terima')->latest()->first();
        $this->assertNotNull($doc);
        $this->assertNull($doc->asset_id);
        $this->assertEquals([$this->peripheral->id], $doc->data['peripheral_ids']);
        $this->assertEquals([], $doc->data['asset_ids']);
    }

    /** @test */
    public function receipt_with_asset_and_empty_peripheral_row_is_valid()
    {
        // Meniru submit dari UI: baris Peripheral dibiarkan kosong (''), hanya Aset diisi.
        $response = $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type'        => 'tanda_terima',
            'asset_ids'            => [$this->asset->id],
            'peripheral_ids'       => [''],
            'recipient_employee_id'=> $this->employee->id,
            'data'                 => ['giver_name' => 'Giver', 'purpose' => 'Kantor'],
            'document_date'        => '2026-08-04',
        ]);

        $response->assertRedirect();

        $doc = SopDocument::where('document_type', 'tanda_terima')->latest()->first();
        $this->assertNotNull($doc);
        $this->assertEquals([$this->asset->id], $doc->data['asset_ids']);
        $this->assertEquals([], $doc->data['peripheral_ids']);
    }

    /** @test */
    public function receipt_with_only_empty_rows_still_requires_selection()
    {
        // Meniru submit dari UI dengan kedua baris kosong ('').
        $response = $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type'        => 'tanda_terima',
            'asset_ids'            => [''],
            'peripheral_ids'       => [''],
            'recipient_employee_id'=> $this->employee->id,
        ]);

        $response->assertSessionHasErrors('asset_ids');
    }

    /** @test */
    public function peripheral_only_receipt_can_be_viewed_and_printed()
    {
        $doc = SopDocument::create([
            'document_type'   => 'tanda_terima',
            'document_number' => 'FTA-2026-0001',
            'recipient_employee_id' => $this->employee->id,
            'document_date'   => now(),
            'data'            => [
                'asset_ids'      => [],
                'peripheral_ids' => [$this->peripheral->id],
            ],
            'created_by'      => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('documents.show', $doc));
        $response->assertStatus(200);
        $response->assertSee('Mouse Wireless');

        $responsePrint = $this->actingAs($this->admin)->get(route('documents.print', $doc));
        $responsePrint->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_mutation_request_document()
    {
        $response = $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type' => 'permohonan_mutasi',
            'asset_ids'     => [$this->asset->id],
            'reason'        => 'Pindah divisi.',
            'data'          => [
                'requester_name'      => 'Pemohon',
                'target_location_id'  => $this->location->id,
                'target_employee_id'  => $this->employee->id,
                'target_status'       => AssetStatus::InUse->value,
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('sop_documents', [
            'document_type' => 'permohonan_mutasi',
            'asset_id'      => $this->asset->id,
        ]);

        $this->asset->refresh();
        $this->assertEquals(AssetStatus::Spare, $this->asset->status, 'Permohonan mutasi tidak boleh mengubah status aset.');
    }

    /** @test */
    public function admin_can_create_berita_acara_document()
    {
        $response = $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type'     => 'berita_acara',
            'mutation_log_ids'  => [$this->mutationLog->id],
            'data'              => ['presenter' => 'Pelaksana', 'witness' => 'Saksi'],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('sop_documents', [
            'document_type'    => 'berita_acara',
            'mutation_log_id'  => $this->mutationLog->id,
        ]);
    }

    /** @test */
    public function mutation_request_requires_reason()
    {
        $response = $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type' => 'permohonan_mutasi',
            'asset_ids'     => [$this->asset->id],
            'reason'        => '',
        ]);

        $response->assertSessionHasErrors('reason');
    }

    /** @test */
    public function admin_can_view_and_download_document()
    {
        $doc = SopDocument::create([
            'document_type'   => 'registrasi',
            'document_number' => 'FRA-2026-0001',
            'asset_id'        => $this->asset->id,
            'document_date'   => now(),
            'created_by'      => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('documents.show', $doc));
        $response->assertStatus(200);
        $response->assertSee('FRA-2026-0001');

        $responsePdf = $this->actingAs($this->admin)->get(route('documents.pdf', $doc));
        $responsePdf->assertStatus(200);
    }

    /** @test */
    public function admin_can_delete_document()
    {
        $doc = SopDocument::create([
            'document_type'   => 'registrasi',
            'document_number' => 'FRA-2026-0002',
            'asset_id'        => $this->asset->id,
            'document_date'   => now(),
            'created_by'      => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('documents.destroy', $doc));

        $response->assertRedirect(route('documents.index'));
        $this->assertSoftDeleted('sop_documents', ['id' => $doc->id]);
    }
}