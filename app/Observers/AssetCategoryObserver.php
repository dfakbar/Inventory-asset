<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Services\AssetCodeGenerator;
use Illuminate\Support\Facades\Log;

class AssetCategoryObserver
{
    public function __construct(
        private readonly AssetCodeGenerator $codeGenerator
    ) {}

    public function updating(AssetCategory $category): void
    {
        $oldAbbr = $category->getOriginal('abbreviation');

        if ($oldAbbr === null || $oldAbbr === $category->abbreviation) {
            return;
        }

        $oldNorm = $this->codeGenerator->normalizeAbbreviation($oldAbbr);
        $newNorm = $this->codeGenerator->normalizeAbbreviation($category->abbreviation);

        if ($oldNorm === $newNorm) {
            return;
        }

        $oldPrefix = 'AST' . $oldNorm;
        $count = 0;

        Asset::where('asset_category_id', $category->id)
            ->where('asset_code', 'like', $oldPrefix . '%')
            ->chunk(200, function ($assets) use ($oldPrefix, $oldNorm, $newNorm, &$count) {
                foreach ($assets as $asset) {
                    $suffix = substr($asset->asset_code, strlen($oldPrefix));
                    $newCode = 'AST' . $newNorm . $suffix;

                    if ($newCode === $asset->asset_code) {
                        continue;
                    }

                    $existing = Asset::withTrashed()->where('asset_code', $newCode)->exists();
                    if ($existing) {
                        $newCode = $this->codeGenerator->generate(
                            $asset->category,
                            $asset->created_at ?? now()
                        );
                    }

                    $asset->asset_code = $newCode;
                    $asset->saveQuietly();
                    $count++;
                }
            });

        Log::info("AssetCategoryObserver: Kode {$count} aset di-regenerate dari {$oldNorm} ke {$newNorm}.", [
            'category' => $category->name,
        ]);
    }
}
