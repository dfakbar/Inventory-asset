<?php

namespace App\Models;

use App\Enums\AssetStatus;
use App\Models\AssetLoan;
use App\Notifications\AssetAssignedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_code',
        'name',
        'asset_category_id',
        'location_id',
        'assigned_to',
        'employee_id',
        'brand_id',
        'vendor_id',
        'model',
        'serial_number',
        'mac_address',
        'purchase_date',
        'purchase_price',
        'quantity',
        'status',
        'notes',
        'image',
        'mutation_date',
    ];

    // =========================================================
    // Casts
    // =========================================================

    protected function casts(): array
    {
        return [
            'status'         => AssetStatus::class,
            'purchase_date'  => 'date',
            'purchase_price' => 'decimal:2',
            'quantity'       => 'integer',
            'mutation_date'  => 'date',
        ];
    }

    // =========================================================
    // Relations
    // =========================================================

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    /**
     * Relasi ke model Location baru (menggantikan AssetLocation).
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function activeLoans(): HasMany
    {
        return $this->hasMany(AssetLoan::class)->whereNull('returned_at');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(AssetLoan::class);
    }

    // =========================================================
    // Query Scopes
    // =========================================================

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('asset_code', 'like', "%{$term}%")
              ->orWhere('name', 'like', "%{$term}%")
              ->orWhere('serial_number', 'like', "%{$term}%")
              ->orWhereHas('brand', fn (Builder $q) => $q->where('name', 'like', "%{$term}%"))
              ->orWhere('model', 'like', "%{$term}%");
        });
    }

    public function scopeOfStatus(Builder $query, ?string $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeOfCategory(Builder $query, ?int $categoryId): Builder
    {
        if (blank($categoryId)) {
            return $query;
        }

        return $query->where('asset_category_id', $categoryId);
    }
}
