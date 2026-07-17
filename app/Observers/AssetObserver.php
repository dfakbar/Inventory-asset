<?php

namespace App\Observers;

use App\Enums\AssetStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetMutationLog;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use App\Notifications\AssetMutationNotification;
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
        // Notification dikirim via queue ke semua admin + PIC saat ini.
        // Aktifkan: set MAIL_MAILER=smtp di .env + konfigurasi SMTP.
        // Jalankan queue worker: php artisan queue:work
        $this->sendMutationNotification($asset, $original, $locationChanged, $assignedChanged, $employeeChanged, $statusChanged);
    }

    private function sendMutationNotification(
        Asset $asset,
        array $original,
        bool $locationChanged,
        bool $assignedChanged,
        bool $employeeChanged,
        bool $statusChanged,
    ): void {
        try {
            $changes = [];

            if ($locationChanged) {
                $oldLocation = Location::find($original['location_id'] ?? null)?->name ?? '-';
                $newLocation = $asset->location?->name ?? '-';
                $changes[] = ['label' => 'Lokasi', 'from' => $oldLocation, 'to' => $newLocation];
            }

            if ($statusChanged) {
                $oldStatus = isset($original['status']) ? AssetStatus::tryFrom($original['status'])?->label() : '-';
                $newStatus = $asset->status?->label() ?? '-';
                $changes[] = ['label' => 'Status', 'from' => $oldStatus, 'to' => $newStatus];
            }

            if ($assignedChanged) {
                $oldUser = User::find($original['assigned_to'] ?? null)?->name ?? '-';
                $newUser = $asset->assignedUser?->name ?? '-';
                $changes[] = ['label' => 'PIC', 'from' => $oldUser, 'to' => $newUser];
            }

            if ($employeeChanged) {
                $oldEmp = Employee::find($original['employee_id'] ?? null)?->name ?? '-';
                $newEmp = $asset->employee?->name ?? '-';
                $changes[] = ['label' => 'Karyawan', 'from' => $oldEmp, 'to' => $newEmp];
            }

            if (empty($changes)) {
                return;
            }

            $performer = Auth::user();
            $performerName = $performer?->name . ' (' . $performer?->email . ')' ?? 'System';

            $notification = new AssetMutationNotification($asset, $changes, $performerName);

            // Kirim ke semua admin
            $adminUsers = User::where('role', UserRole::Admin)->get();
            foreach ($adminUsers as $admin) {
                if ($admin->id !== Auth::id() && $admin->email) {
                    $admin->notify($notification);
                    Log::info("Notifikasi mutasi dikirim ke admin {$admin->email} untuk aset {$asset->asset_code}.");
                }
            }

            // Kirim ke PIC saat ini (jika ada dan bukan admin yang sudah dikirimi)
            if ($asset->assigned_to && $asset->assigned_to !== Auth::id()) {
                $currentPIC = $asset->assignedUser;
                if ($currentPIC && $currentPIC->email) {
                    $currentPIC->notify($notification);
                    Log::info("Notifikasi mutasi dikirim ke PIC {$currentPIC->email} untuk aset {$asset->asset_code}.");
                }
            }

        } catch (\Throwable $e) {
            Log::warning("Gagal mengirim notifikasi mutasi untuk aset {$asset->asset_code}.", ['error' => $e->getMessage()]);
        }
    }
}
