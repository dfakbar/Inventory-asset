<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTrackTest extends TestCase
{
    use RefreshDatabase;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $category = AssetCategory::create(['name' => 'Laptop', 'abbreviation' => 'LAP']);

        $this->asset = Asset::create([
            'name'              => 'Laptop Tracking Test',
            'asset_category_id' => $category->id,
            'status'            => AssetStatus::InUse->value,
            'quantity'          => 1,
            'serial_number'     => 'SN-TRACK-0001',
            'mac_address'       => 'AA:BB:CC:DD:EE:FF',
        ]);
    }

    /** @test */
    public function track_asset_by_asset_code()
    {
        $response = $this->get(route('public.track', ['search' => $this->asset->asset_code]));

        $response->assertOk();
        $response->assertSee($this->asset->asset_code);
        $response->assertSee('Laptop Tracking Test');
    }

    /** @test */
    public function track_asset_by_serial_number()
    {
        $response = $this->get(route('public.track', ['search' => 'SN-TRACK-0001']));

        $response->assertOk();
        $response->assertSee($this->asset->asset_code);
        $response->assertSee('Laptop Tracking Test');
    }

    /** @test */
    public function track_asset_by_mac_address_case_and_format_insensitive()
    {
        $response = $this->get(route('public.track', ['search' => 'aa-bb-cc-dd-ee-ff']));

        $response->assertOk();
        $response->assertSee($this->asset->asset_code);
        $response->assertSee('Laptop Tracking Test');
    }

    /** @test */
    public function track_returns_not_found_for_unknown_term()
    {
        $response = $this->get(route('public.track', ['search' => 'TIDAK-ADA-123']));

        $response->assertOk();
        $response->assertSee('Aset Tidak Ditemukan');
        $response->assertDontSee('Laptop Tracking Test');
    }
}