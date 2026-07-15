<?php

namespace App\Notifications;

use App\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssetAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Asset $asset
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Aset {$this->asset->asset_code} — {$this->asset->name} ditugaskan kepada Anda")
            ->greeting("Halo {$notifiable->name},")
            ->line("Aset berikut telah ditugaskan kepada Anda:")
            ->line("**{$this->asset->asset_code}** — {$this->asset->name}")
            ->line("Status: **{$this->asset->status->label()}**")
            ->when($this->asset->brand, fn ($msg) => $msg->line("Merek: {$this->asset->brand->name}"))
            ->when($this->asset->model, fn ($msg) => $msg->line("Model: {$this->asset->model}"))
            ->when($this->asset->location, fn ($msg) => $msg->line("Lokasi: {$this->asset->location->name}"))
            ->action('Lihat Detail Aset', route('assets.show', $this->asset))
            ->line('Silakan periksa kondisi aset dan laporkan jika ada ketidaksesuaian.')
            ->line('Terima kasih.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'asset_id'   => $this->asset->id,
            'asset_code' => $this->asset->asset_code,
            'asset_name' => $this->asset->name,
        ];
    }
}
