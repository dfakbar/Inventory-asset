<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\AssetMutationLog;
use App\Models\User;
use App\Services\AssetCodeGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Observer yang secara otomatis men-generate kode aset unik
 * setiap kali aset baru akan disimpan ke database.
 *
 * Didaftarkan di AppServiceProvider::boot().
 * PENTING: Asset::create() harus dipanggil di dalam DB::transaction()
 *          agar lockForUpdate() di AssetCodeGenerator bekerja dengan benar.
 */
class AssetObserver
{
    public function __construct(
        private readonly AssetCodeGenerator $codeGenerator
    ) {}

    /**
     * Event "creating" dipicu sebelum INSERT ke database.
     * Di sinilah kode aset di-generate secara otomatis.
     */
    public function creating(Asset $asset): void
    {
        // Jika kode sudah di-set secara manual, jangan override.
        if (!empty($asset->asset_code)) {
            return;
        }

        // Lazy-load relasi category menggunakan foreign key yang sudah di-set.
        $category = $asset->category;

        if ($category === null) {
            // Ini seharusnya tidak terjadi karena validasi sudah menjaganya.
            Log::error('AssetObserver: Gagal generate kode - kategori tidak ditemukan.', [
                'asset_category_id' => $asset->asset_category_id,
            ]);
            throw new \RuntimeException('Kategori aset tidak ditemukan. Kode tidak dapat di-generate.');
        }

        $asset->asset_code = $this->codeGenerator->generate($category, now());

        Log::info("AssetObserver: Kode aset {$asset->asset_code} berhasil di-generate.", [
            'category' => $category->name,
        ]);
    }

    /**
     * Event "updated" dipicu setelah UPDATE ke database.
     * Mencatat log mutasi jika terdapat perubahan lokasi, penugasan, atau status.
     */
    public function updated(Asset $asset): void
    {
        $locationChanged   = $asset->wasChanged('location_id');
        $assignedChanged   = $asset->wasChanged('assigned_to');
        $employeeChanged   = $asset->wasChanged('employee_id');
        $statusChanged     = $asset->wasChanged('status');
        $mutationDateSet   = $asset->wasChanged('mutation_date');

        // Catat mutasi hanya jika ada perubahan yang relevan
        if (! $locationChanged && ! $assignedChanged && ! $employeeChanged && ! $statusChanged && ! $mutationDateSet) {
            return;
        }

        $original = $asset->getOriginal();

        AssetMutationLog::create([
            'asset_id'          => $asset->id,
            'performed_by'      => Auth::id(),
            'from_location_id'  => $original['location_id'] ?? null,
            'to_location_id'    => $asset->location_id,
            'from_assigned_to'  => $original['assigned_to'] ?? null,
            'to_assigned_to'    => $asset->assigned_to,
            'from_employee_id'  => $original['employee_id'] ?? null,
            'to_employee_id'    => $asset->employee_id,
            'from_status'       => $original['status'] ?? null,
            'to_status'         => $asset->status->value,
            'mutation_date'     => $asset->mutation_date,
        ]);

        Log::info("AssetObserver: Log mutasi dicatat untuk aset {$asset->asset_code}.", [
            'location_changed' => $locationChanged,
            'assigned_changed' => $assignedChanged,
            'status_changed'   => $statusChanged,
        ]);

        // ── EMAIL NOTIFICATION ────────────────────────────────────
        // Notification dikirim via queue ke user yang ditugaskan.
        // Aktifkan dengan: set MAIL_MAILER=smtp di .env + konfigurasi SMTP.
        // Jalankan queue worker: php artisan queue:work
        if ($assignedChanged) {
            try {
                if ($asset->assigned_to) {
                    $newUser = User::find($asset->assigned_to);
                    if ($newUser && $newUser->email) {
                        $newUser->notify(new \App\Notifications\AssetAssignedNotification($asset));
                        Log::info("Notifikasi email dikirim ke {$newUser->email} untuk aset {$asset->asset_code}.");
                    }
                }
                $previousUserId = $original['assigned_to'] ?? null;
                if ($previousUserId && $previousUserId != $asset->assigned_to) {
                    $previousUser = User::find($previousUserId);
                    if ($previousUser && $previousUser->email) {
                        $previousUser->notify(new \App\Notifications\AssetAssignedNotification($asset));
                        Log::info("Notifikasi email dikirim ke {$previousUser->email} (previous PIC) untuk aset {$asset->asset_code}.");
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim notifikasi email untuk aset {$asset->asset_code}.", ['error' => $e->getMessage()]);
            }
        }
    }
}
