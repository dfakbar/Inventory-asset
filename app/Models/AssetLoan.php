<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetLoan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'borrower_name',
        'borrower_email',
        'loan_date',
        'expected_return_date',
        'returned_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'loan_date'             => 'date',
            'expected_return_date'  => 'date',
            'returned_at'           => 'date',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->returned_at === null;
    }
}
