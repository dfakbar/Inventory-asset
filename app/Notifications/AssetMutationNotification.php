<?php

namespace App\Notifications;

use App\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssetMutationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Asset $asset,
        public array $changes,
        public string $performerName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $categoryName = $this->asset->category?->name ?? '-';
        $serialNumber = $this->asset->serial_number ?? '-';
        $mutationDate = $this->asset->mutation_date?->format('d-m-Y') ?? now()->format('d-m-Y');

        $mail = (new MailMessage)
            ->subject("Mutasi Aset: {$this->asset->asset_code} — {$this->asset->name}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Aset berikut telah mengalami perubahan data:")
            ->line("");

        $mail->line("Kode Aset : {$this->asset->asset_code}");
        $mail->line("Nama Aset : {$this->asset->name}");
        $mail->line("Kategori  : {$categoryName}");
        $mail->line("Serial No : {$serialNumber}");

        $mail->line("");
        $mail->line("---");
        $mail->line("Perubahan yang terjadi:");
        $mail->line("");

        foreach ($this->changes as $change) {
            $mail->line("- {$change['label']}: {$change['from']} -> {$change['to']}");
        }

        $mail->line("");
        $mail->line("---");
        $mail->line("Tanggal Mutasi : {$mutationDate}");
        $mail->line("Diubah oleh    : {$this->performerName}");

        $mail->action('Lihat Detail Aset', route('assets.show', $this->asset));

        $mail->line("(c) AssetMS - Sistem Informasi Manajemen Aset");

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'asset_id'   => $this->asset->id,
            'asset_code' => $this->asset->asset_code,
            'asset_name' => $this->asset->name,
            'changes'    => $this->changes,
        ];
    }
}
