<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\AssetMutationLog;
use Illuminate\Console\Command;

class PurgeLogs extends Command
{
    protected $signature = 'logs:purge';

    protected $description = 'Permanently delete soft-deleted logs older than 30 days';

    public function handle(): void
    {
        $cutoff = now()->subDays(30);

        $activityDeleted = ActivityLog::onlyTrashed()
            ->where('deleted_at', '<', $cutoff)
            ->forceDelete();

        $mutationDeleted = AssetMutationLog::onlyTrashed()
            ->where('deleted_at', '<', $cutoff)
            ->forceDelete();

        $this->info("Purged {$activityDeleted} activity logs and {$mutationDeleted} mutation logs.");
    }
}
