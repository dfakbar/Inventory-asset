<?php

use App\Providers\AppServiceProvider;
use Sentry\Laravel\ServiceProvider as SentryServiceProvider;
use Sentry\Laravel\Tracing\ServiceProvider as SentryTracingServiceProvider;

return [
    AppServiceProvider::class,
    SentryServiceProvider::class,
    SentryTracingServiceProvider::class,
];
