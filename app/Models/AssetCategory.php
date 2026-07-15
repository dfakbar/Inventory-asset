<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation',
        'description',
    ];

    // =========================================================
    // Mutators
    // =========================================================

    /**
     * Selalu simpan abbreviation dalam huruf kapital.
     */
    public function setAbbreviationAttribute(string $value): void
    {
        $this->attributes['abbreviation'] = strtoupper(trim($value));
    }

    // =========================================================
    // Accessors
    // =========================================================

    /**
     * Kembalikan abbreviation 3 huruf yang sudah diformat untuk kode aset.
     */
    public function getFormattedAbbreviationAttribute(): string
    {
        return strtoupper(substr($this->abbreviation, 0, 3));
    }

    // =========================================================
    // Relations
    // =========================================================

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
