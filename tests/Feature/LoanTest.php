<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLoan;
use App\Models\Brand;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);

        $category = AssetCategory::create(['name' => 'Test', 'abbreviation' => 'TST']);
        Brand::create(['name' => 'Test Brand']);

        $this->asset = Asset::create([
            'name'              => 'Test Asset',
            'asset_category_id' => $category->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
        ]);
    }

    /** @test */
    public function admin_can_check_out_asset()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('loans.store'), [
                'asset_id'      => $this->asset->id,
                'borrower_name' => 'John Doe',
                'loan_date'     => '2026-07-01',
            ]);

        $response->assertRedirect(route('loans.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('asset_loans', [
            'asset_id'      => $this->asset->id,
            'borrower_name' => 'John Doe',
        ]);

        $this->asset->refresh();
        $this->assertEquals(AssetStatus::InUse, $this->asset->status);
    }

    /** @test */
    public function cannot_check_out_already_loaned_asset()
    {
        AssetLoan::create([
            'asset_id'      => $this->asset->id,
            'borrower_name' => 'First Borrower',
            'loan_date'     => '2026-07-01',
            'created_by'    => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('loans.store'), [
                'asset_id'      => $this->asset->id,
                'borrower_name' => 'Second Borrower',
                'loan_date'     => '2026-07-15',
            ]);

        $response->assertSessionHasErrors('asset_id');
        $this->assertEquals(1, AssetLoan::count());
    }

    /** @test */
    public function admin_can_check_in_asset()
    {
        $loan = AssetLoan::create([
            'asset_id'      => $this->asset->id,
            'borrower_name' => 'John Doe',
            'loan_date'     => '2026-07-01',
            'created_by'    => $this->admin->id,
        ]);

        $this->asset->update(['status' => AssetStatus::InUse]);

        $response = $this->actingAs($this->admin)
            ->patch(route('loans.checkin', $loan));

        $response->assertRedirect(route('loans.index'));

        $loan->refresh();
        $this->assertNotNull($loan->returned_at);

        $this->asset->refresh();
        $this->assertEquals(AssetStatus::Spare, $this->asset->status);
    }

    /** @test */
    public function cannot_check_in_already_checked_in_asset()
    {
        $loan = AssetLoan::create([
            'asset_id'      => $this->asset->id,
            'borrower_name' => 'John Doe',
            'loan_date'     => '2026-07-01',
            'returned_at'   => '2026-07-10',
            'created_by'    => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('loans.checkin', $loan));

        $response->assertSessionHas('error');
    }

    /** @test */
    public function loan_index_can_filter_by_search()
    {
        AssetLoan::create([
            'asset_id'      => $this->asset->id,
            'borrower_name' => 'Unique Borrower X',
            'loan_date'     => '2026-07-01',
            'created_by'    => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('loans.index', ['search' => 'Unique']));

        $response->assertStatus(200);
        $response->assertSee('Unique Borrower X');
    }

    /** @test */
    public function loan_index_can_filter_by_date_range()
    {
        AssetLoan::create([
            'asset_id'      => $this->asset->id,
            'borrower_name' => 'Date Filter',
            'loan_date'     => '2026-07-15',
            'created_by'    => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('loans.index', ['date_from' => '2026-07-01', 'date_to' => '2026-07-20']));

        $response->assertStatus(200);
        $response->assertSee('Date Filter');
    }
}
