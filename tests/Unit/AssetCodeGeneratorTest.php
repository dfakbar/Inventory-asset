<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Services\AssetCodeGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AssetCodeGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private AssetCodeGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new AssetCodeGenerator();

        // Run essential migrations for the assets table
        $this->artisan('migrate', ['--path' => 'database/migrations/0001_01_01_000000_create_users_table.php', '--realpath' => true]);
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_01_01_000001_create_asset_categories_table.php', '--realpath' => true]);
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_01_01_000003_create_assets_table.php', '--realpath' => true]);
    }

    #[Test]
    public function it_normalizes_abbreviation_to_3_uppercase_chars(): void
    {
        $category = AssetCategory::make(['abbreviation' => 'mon']);
        $date = Carbon::create(2026, 3, 1);
        $code = $this->generator->generate($category, $date);

        $this->assertStringStartsWith('ASTMON', $code);
    }

    #[Test]
    public function it_pads_short_abbreviation_with_x(): void
    {
        $category = AssetCategory::make(['abbreviation' => 'UP']);
        $date = Carbon::create(2026, 3, 1);
        $code = $this->generator->generate($category, $date);

        $this->assertStringStartsWith('ASTUPX', $code);
    }

    #[Test]
    public function it_truncates_long_abbreviation_to_3_chars(): void
    {
        $category = AssetCategory::make(['abbreviation' => 'SERVER']);
        $date = Carbon::create(2026, 3, 1);
        $code = $this->generator->generate($category, $date);

        $this->assertStringStartsWith('ASTSER', $code);
    }

    #[Test]
    public function it_includes_year_and_month_in_code(): void
    {
        $category = AssetCategory::make(['abbreviation' => 'LPT']);
        $date = Carbon::create(2026, 12, 25);
        $code = $this->generator->generate($category, $date);

        $this->assertStringContainsString('2612', $code);
    }

    #[Test]
    public function it_starts_sequence_at_01(): void
    {
        $category = AssetCategory::make(['abbreviation' => 'TST']);
        $date = Carbon::create(2026, 7, 1);
        $code = $this->generator->generate($category, $date);

        $this->assertStringEndsWith('01', $code);
    }

    #[Test]
    public function it_removes_special_chars_from_abbreviation(): void
    {
        $category = AssetCategory::make(['abbreviation' => 'mon@#$']);
        $date = Carbon::create(2026, 3, 1);
        $code = $this->generator->generate($category, $date);

        $this->assertStringStartsWith('ASTMON', $code);
    }

    #[Test]
    public function it_uses_current_date_when_none_provided(): void
    {
        $category = AssetCategory::make(['abbreviation' => 'NOW']);
        $code = $this->generator->generate($category);

        $this->assertStringStartsWith('ASTNOW', $code);
        $this->assertMatchesRegularExpression('/^ASTNOW\d{4}01$/', $code);
    }
}
