<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    public const DIVISIONS = [
        'PROD PN1', 'PROD PN2', 'QMS', 'FAT', 'IT', 'SCM', 'PPIC',
        'R&D', 'WHFG', 'WHRM', 'ENGINEERING', 'PROJECT', 'PURCHASIING',
        'HRGA', 'MR', 'PROD BMSD',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'department',
        'position',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'employee_id');
    }
}
