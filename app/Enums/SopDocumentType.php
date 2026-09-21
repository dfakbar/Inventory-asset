<?php

namespace App\Enums;

/**
 * Jenis dokumen yang mendukung SOP Aset IT.
 *
 * - registrasi: Form Registrasi Aset
 * - tanda_terima: Form Tanda Terima Aset
 * - permohonan_mutasi: Form Permohonan Mutasi Aset
 * - berita_acara: Berita Acara Mutasi Aset
 * - peminjaman: Form Peminjaman Aset (diterbitkan otomatis saat check-out)
 */
enum SopDocumentType: string
{
    case Registrasi        = 'registrasi';
    case TandaTerima       = 'tanda_terima';
    case PermohonanMutasi  = 'permohonan_mutasi';
    case BeritaAcara       = 'berita_acara';
    case Peminjaman        = 'peminjaman';

    public function label(): string
    {
        return match ($this) {
            self::Registrasi       => 'Form Registrasi Aset',
            self::TandaTerima      => 'Form Tanda Terima Aset',
            self::PermohonanMutasi => 'Form Permohonan Mutasi Aset',
            self::BeritaAcara      => 'Berita Acara Mutasi Aset',
            self::Peminjaman       => 'Form Peminjaman Aset',
        };
    }

    /**
     * Prefix untuk penomoran dokumen otomatis.
     */
    public function prefix(): string
    {
        return match ($this) {
            self::Registrasi       => 'FRA',
            self::TandaTerima      => 'FTA',
            self::PermohonanMutasi => 'FPM',
            self::BeritaAcara      => 'BAMA',
            self::Peminjaman       => 'FPN',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Registrasi       => 'bi-clipboard-check',
            self::TandaTerima      => 'bi-table',
            self::PermohonanMutasi => 'bi-send',
            self::BeritaAcara      => 'bi-journal-check',
            self::Peminjaman       => 'bi-box-arrow-right',
        };
    }

    /**
     * Pasangan kode aset + tipe yang membutuhkan asset.
     */
    public function requiresAsset(): bool
    {
        return in_array($this, [self::Registrasi, self::TandaTerima, self::PermohonanMutasi], true);
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}