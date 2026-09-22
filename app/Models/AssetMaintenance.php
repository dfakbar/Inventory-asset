<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetMaintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'performed_by',
        'action_type',
        'component_name',
        'previous_spec',
        'new_spec',
        'cost',
        'maintenance_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_date' => 'date',
            'cost'             => 'decimal:2',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
