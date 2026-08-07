<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class Controller
{
    use AuthorizesRequests;

    protected const PER_PAGE_OPTIONS = ['15', '30', '60', '120', 'all'];
    protected const DEFAULT_PER_PAGE = 15;

    /**
     * Paginate a query with the per_page selector value
     * (15/30/60/120/Semua). For "all", uses the full record count so
     * perPage() stays correct for row numbering in views.
     */
    protected function paginateQuery(Request $request, Builder $query): LengthAwarePaginator
    {
        $value = $request->query('per_page');

        if ($value === 'all') {
            $perPage = max(1, $query->count());
        } elseif (in_array($value, self::PER_PAGE_OPTIONS, true)) {
            $perPage = (int) $value;
        } else {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
