<?php

namespace App\Models;

use App\Enums\SopDocumentType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SopDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_type',
        'document_number',
        'asset_id',
        'mutation_log_id',
        'recipient_employee_id',
        'document_date',
        'reason',
        'notes',
        'data',
        'pdf_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'document_type'  => SopDocumentType::class,
            'document_date'  => 'date',
            'data'           => 'array',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function mutationLog(): BelongsTo
    {
        return $this->belongsTo(AssetMutationLog::class, 'mutation_log_id');
    }

    public function recipientEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'recipient_employee_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getAssetsAttribute(): Collection
    {
        $ids = $this->data['asset_ids'] ?? ($this->asset_id ? [$this->asset_id] : []);
        return Asset::whereIn('id', $ids)
            ->with(['category', 'brand', 'location', 'vendor', 'assignedUser', 'employee'])
            ->get();
    }

    public function getPeripheralsAttribute(): Collection
    {
        $ids = $this->data['peripheral_ids'] ?? [];
        return Peripheral::with(['brand:id,name', 'location:id,name'])
            ->whereIn('id', $ids)
            ->get();
    }

    public function getLogsAttribute(): Collection
    {
        $ids = $this->data['mutation_log_ids'] ?? ($this->mutation_log_id ? [$this->mutation_log_id] : []);
        return AssetMutationLog::with([
                'asset:id,asset_code,name,model,asset_category_id,brand_id',
                'asset.category:id,name',
                'asset.brand:id,name',
                'fromLocation:id,name',
                'toLocation:id,name',
                'fromAssignedUser:id,name',
                'toAssignedUser:id,name',
                'fromEmployee:id,name',
                'toEmployee:id,name',
                'performedBy:id,name',
            ])
            ->whereIn('id', $ids)
            ->get();
    }

    public function pdfUrl(): ?string
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : null;
    }
}