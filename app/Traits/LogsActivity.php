<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->logAction('created', class_basename($model) . ' created');
        });

        static::updated(function ($model) {
            $changed = $model->getDirty();
            unset($changed['updated_at']);
            if (!empty($changed)) {
                $description = class_basename($model) . ' updated: ' . implode(', ', array_keys($changed));
                $model->logAction('updated', $description);
            }
        });

        static::deleted(function ($model) {
            $model->logAction('deleted', class_basename($model) . ' deleted');
        });
    }

    protected function logAction(string $action, string $description): void
    {
        try {
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => $action,
                'description' => $description,
                'model_type'  => static::class,
                'model_id'    => $this->getKey(),
                'ip_address'  => request()?->ip(),
                'user_agent'  => request()?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat activity log.', ['error' => $e->getMessage()]);
        }
    }
}
