<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk mencatat riwayat mutasi (perpindahan) aset.
 * Setiap perubahan lokasi atau penugasan aset dicatat di sini.
 */
class AssetMutationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'performed_by',
        'from_location_id',
        'to_location_id',
        'from_assigned_to',
        'to_assigned_to',
        'from_employee_id',
        'to_employee_id',
        'from_status',
        'to_status',
        'mutation_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'mutation_date' => 'date',
        ];
    }

    // =========================================================
    // Relations
    // =========================================================

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function fromAssignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_assigned_to');
    }

    public function toAssignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_assigned_to');
    }

    public function fromEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'from_employee_id');
    }

    public function toEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'to_employee_id');
    }
}
