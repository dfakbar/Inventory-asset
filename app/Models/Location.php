<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department',
        'slug',
        'description',
    ];

    // =========================================================
    // Boot — auto-generate slug
    // =========================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $location): void {
            if (empty($location->slug)) {
                $location->slug = static::generateUniqueSlug($location->name);
            }
        });

        static::updating(function (self $location): void {
            // Re-generate slug hanya jika nama berubah & slug tidak di-set manual
            if ($location->isDirty('name') && !$location->isDirty('slug')) {
                $location->slug = static::generateUniqueSlug($location->name, $location->id);
            }
        });
    }

    // =========================================================
    // Helpers
    // =========================================================

    /**
     * Generate slug unik dengan suffix angka jika sudah ada yang sama.
     * Contoh: "ruang-server", "ruang-server-1", "ruang-server-2", …
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base    = Str::slug($name);
        $slug    = $base;
        $counter = 1;

        while (
            static::when(
                $ignoreId,
                fn ($q) => $q->where('id', '!=', $ignoreId)
            )
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    // =========================================================
    // Relations
    // =========================================================

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
