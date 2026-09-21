<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLoan;
use App\Models\Brand;
use App\Models\SopDocument;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanFormTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'username' => 'admin-loanform',
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);

        $this->staff = User::create([
            'name'     => 'Staff',
            'email'    => 'staff@test.com',
            'password' => bcrypt('password'),
            'username' => 'staff-loanform',
            'role'     => UserRole::Staff,
        ]);
        $this->staff->assignRole(UserRole::Staff->value);

        $category = AssetCategory::create(['name' => 'Laptop', 'abbreviation' => 'LPT']);
        Brand::create(['name' => 'Dell']);

        $this->asset = Asset::create([
            'name'              => 'Laptop Pinjam',
            'asset_category_id' => $category->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
            'assigned_to'       => $this->admin->id,
        ]);
    }

    /** @test */
    public function checkout_auto_creates_loan_form_with_fpn_number()
    {
        $response = $this->actingAs($this->admin)->post(route('loans.store'), [
            'asset_id'      => $this->asset->id,
            'borrower_name' => 'Budi Peminjam',
            'loan_date'     => '2026-09-01',
        ]);

        $loan = AssetLoan::latest()->first();
        $response->assertRedirect(route('loans.show', $loan));

        $this->assertDatabaseHas('sop_documents', [
            'document_type' => 'peminjaman',
            'loan_id'       => $loan->id,
            'asset_id'      => $this->asset->id,
        ]);

        $doc = SopDocument::where('document_type', 'peminjaman')->first();
        $this->assertMatchesRegularExpression('/^FPN-\d{4}-\d{2}-\d{4}$/', $doc->document_number);
        $this->assertNotNull($doc->pdf_path);
        $this->assertEquals($loan->id, $doc->data['loan_id']);
        $this->assertEquals([$this->asset->id], $doc->data['asset_ids']);
    }

    /** @test */
    public function loan_form_can_be_printed_and_downloaded()
    {
        $this->actingAs($this->admin)->post(route('loans.store'), [
            'asset_id'      => $this->asset->id,
            'borrower_name' => 'Budi Peminjam',
            'loan_date'     => '2026-09-01',
        ]);

        $loan = AssetLoan::latest()->first();

        $print = $this->actingAs($this->admin)->get(route('loans.form-print', $loan));
        $print->assertStatus(200);

        $pdf = $this->actingAs($this->admin)->get(route('loans.form-pdf', $loan));
        $pdf->assertStatus(200);
    }

    /** @test */
    public function retroactive_form_can_be_created_for_old_loan()
    {
        $loan = AssetLoan::create([
            'asset_id'      => $this->asset->id,
            'borrower_name' => 'Peminjam Lama',
            'loan_date'     => '2026-08-01',
            'created_by'    => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('loans.form-create', $loan));

        $response->assertRedirect(route('loans.show', $loan));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sop_documents', [
            'document_type' => 'peminjaman',
            'loan_id'       => $loan->id,
        ]);
    }

    /** @test */
    public function retroactive_form_is_rejected_when_form_already_exists()
    {
        $this->actingAs($this->admin)->post(route('loans.store'), [
            'asset_id'      => $this->asset->id,
            'borrower_name' => 'Budi Peminjam',
            'loan_date'     => '2026-09-01',
        ]);

        $loan = AssetLoan::latest()->first();

        $response = $this->actingAs($this->admin)->post(route('loans.form-create', $loan));

        $response->assertSessionHas('error');
        $this->assertEquals(1, SopDocument::where('document_type', 'peminjaman')->count());
    }

    /** @test */
    public function staff_without_permission_cannot_access_loan_form()
    {
        $this->actingAs($this->admin)->post(route('loans.store'), [
            'asset_id'      => $this->asset->id,
            'borrower_name' => 'Budi Peminjam',
            'loan_date'     => '2026-09-01',
        ]);

        $loan = AssetLoan::latest()->first();

        $this->actingAs($this->staff)->get(route('loans.form-print', $loan))->assertForbidden();
        $this->actingAs($this->staff)->get(route('loans.form-pdf', $loan))->assertForbidden();
        $this->actingAs($this->staff)->post(route('loans.form-create', $loan))->assertForbidden();
    }

    /** @test */
    public function manual_creation_of_loan_form_via_documents_is_rejected()
    {
        $response = $this->actingAs($this->admin)->post(route('documents.store'), [
            'document_type' => 'peminjaman',
            'asset_ids'     => [$this->asset->id],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('sop_documents', ['document_type' => 'peminjaman']);
    }

    /** @test */
    public function loan_form_appears_in_documents_archive()
    {
        $this->actingAs($this->admin)->post(route('loans.store'), [
            'asset_id'      => $this->asset->id,
            'borrower_name' => 'Budi Peminjam',
            'loan_date'     => '2026-09-01',
        ]);

        $doc = SopDocument::where('document_type', 'peminjaman')->first();

        $response = $this->actingAs($this->admin)->get(route('documents.show', $doc));
        $response->assertStatus(200);
        $response->assertSee($doc->document_number);
        $response->assertSee('Budi Peminjam');
    }
}
