<?php

use App\Console\Commands\PurgeLogs;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// app/Console/Kernel.php TIDAK dipakai di bootstrap Laravel 12 (withKernels() men-bind
// Illuminate\Foundation\Console\Kernel), jadi schedule didaftarkan di sini.
Schedule::command(PurgeLogs::class)->daily();
