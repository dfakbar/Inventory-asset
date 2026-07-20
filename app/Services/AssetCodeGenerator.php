<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use Carbon\Carbon;

/**
 * Service yang bertanggung jawab untuk menghasilkan kode aset yang unik dan berurutan.
 *
 * FORMAT: AST{ABR}{YY}{MM}{SEQ}
 * Contoh: ASTMON260301
 *   - AST  = Prefix tetap (Aset)
 *   - MON  = Singkatan kategori 3 huruf (Monitor)
 *   - 26   = Tahun 2 digit (2026)
 *   - 03   = Bulan 2 digit (Maret)
 *   - 01   = Urutan datang di bulan tersebut (2 digit minimum)
 *
 * CONCURRENCY SAFETY:
 *   Method generate() HARUS dipanggil di dalam DB::transaction().
 *   lockForUpdate() memastikan tidak ada race condition pada MySQL/PostgreSQL.
 *   Pada SQLite (development), lockForUpdate() diabaikan secara diam-diam.
 */
class AssetCodeGenerator
{
    private const PREFIX       = 'AST';
    private const ABBR_LENGTH  = 3;
    private const SEQ_PAD      = 2;

    /**
     * Generate kode aset unik berdasarkan kategori dan tanggal.
     *
     * @throws \RuntimeException jika gagal generate kode.
     */
    public function generate(AssetCategory $category, ?Carbon $date = null): string
    {
        $date = $date ?? now();

        $abbr      = $this->normalizeAbbreviation($category->abbreviation);
        $year      = $date->format('y');   // 2 digit tahun, e.g. "26"
        $month     = $date->format('m');   // 2 digit bulan, e.g. "03"

        $codePrefix = self::PREFIX . $abbr . $year . $month;

        // Ambil kode terakhir dengan prefix yang sama (termasuk yang sudah dihapus soft-delete)
        // lockForUpdate() mencegah race condition di dalam transaksi DB.
        $lastCode = Asset::withTrashed()
            ->where('asset_code', 'like', $codePrefix . '%')
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->value('asset_code');

        $nextSequence = $this->resolveNextSequence($lastCode, strlen($codePrefix));

        return $codePrefix . str_pad($nextSequence, self::SEQ_PAD, '0', STR_PAD_LEFT);
    }

    /**
     * Normalisasi abbreviation menjadi tepat 3 huruf kapital.
     * "mon" → "MON", "lp" → "LPX", "laptop" → "LAP"
     */
    public function normalizeAbbreviation(string $abbreviation): string
    {
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $abbreviation));
        return str_pad(substr($clean, 0, self::ABBR_LENGTH), self::ABBR_LENGTH, 'X');
    }

    /**
     * Tentukan nomor urutan berikutnya dari kode terakhir yang ada.
     */
    private function resolveNextSequence(?string $lastCode, int $prefixLength): int
    {
        if ($lastCode === null) {
            return 1;
        }

        $lastSequence = (int) substr($lastCode, $prefixLength);

        return max($lastSequence + 1, 1);
    }
}
