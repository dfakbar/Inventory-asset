<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Brand;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsvImportExportTest extends TestCase
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
            'role'     => UserRole::Admin,
        ]);
        $this->admin->assignRole(UserRole::Admin->value);
    }

    private function createCsvStream(array $rows): string
    {
        $header = ['Kode Aset,Nama,Kategori,Merek,Model,Serial Number,Lokasi,Vendor,Status,Tanggal Pembelian,Harga Pembelian,Jumlah,Catatan'];
        $lines = array_merge($header, $rows);
        return implode("\n", $lines);
    }

    /** @test */
    public function admin_can_export_csv()
    {
        $category = AssetCategory::create(['name' => 'Monitor', 'abbreviation' => 'MON']);
        Brand::create(['name' => 'Dell']);

        Asset::create([
            'name'              => 'Test Asset',
            'asset_category_id' => $category->id,
            'status'            => AssetStatus::Spare->value,
            'quantity'          => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.export.csv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename=export-aset-', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function admin_can_import_csv()
    {
        $category = AssetCategory::create(['name' => 'Monitor', 'abbreviation' => 'MON']);

        $csv = $this->createCsvStream([
            ',Monitor Baru,Monitor,Dell,UltraSharp,SN001,,,Spare,2026-01-15,5000000,1,Catatan test',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('assets.import.csv'), [
                'csv_file' => \Illuminate\Http\UploadedFile::fake()->createWithContent('test.csv', $csv),
            ]);

        $response->assertRedirect(route('assets.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('assets', ['name' => 'Monitor Baru']);
    }

    /** @test */
    public function csv_import_skips_invalid_category()
    {
        $csv = $this->createCsvStream([
            ',Unknown Cat Asset,NonExistentCategory,,,,,,,,,',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('assets.import.csv'), [
                'csv_file' => \Illuminate\Http\UploadedFile::fake()->createWithContent('test.csv', $csv),
            ]);

        $response->assertRedirect(route('assets.index'));
        $this->assertDatabaseMissing('assets', ['name' => 'Unknown Cat Asset']);
    }

    /** @test */
    public function csv_import_uses_default_status_for_invalid_status()
    {
        $category = AssetCategory::create(['name' => 'Monitor', 'abbreviation' => 'MON']);

        $csv = $this->createCsvStream([
            ',Asset Bad Status,Monitor,,,,,,InvalidStatus,,,,,',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('assets.import.csv'), [
                'csv_file' => \Illuminate\Http\UploadedFile::fake()->createWithContent('test.csv', $csv),
            ]);

        $response->assertRedirect(route('assets.index'));

        $this->assertDatabaseHas('assets', [
            'name'   => 'Asset Bad Status',
            'status' => AssetStatus::Spare->value,
        ]);
    }

    /** @test */
    public function csv_import_handles_valid_date()
    {
        $category = AssetCategory::create(['name' => 'Monitor', 'abbreviation' => 'MON']);

        $csv = $this->createCsvStream([
            ',Dated Asset,Monitor,,,,,2026-06-15,Spare,2026-01-01,1000000,1,',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('assets.import.csv'), [
                'csv_file' => \Illuminate\Http\UploadedFile::fake()->createWithContent('test.csv', $csv),
            ]);

        $response->assertRedirect(route('assets.index'));

        $asset = Asset::where('name', 'Dated Asset')->first();
        $this->assertNotNull($asset);
        $this->assertEquals('2026-01-01', $asset->purchase_date->format('Y-m-d'));
    }
}
