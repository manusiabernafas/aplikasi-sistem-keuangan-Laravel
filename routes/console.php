<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('keuang:process-recurring-transactions')->dailyAt('01:00');
Schedule::command('keuang:check-alerts')->dailyAt('06:00');
