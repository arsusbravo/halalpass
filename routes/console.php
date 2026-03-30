<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Console Routes / Scheduled Tasks
|--------------------------------------------------------------------------
|
| Add these schedule definitions to your routes/console.php file.
| Laravel 12 uses routes/console.php instead of app/Console/Kernel.php.
|
*/

// Daily at 6 AM: check certificate expiry + recalculate scores + notify suppliers
// Schedule::command('halal:check-expiry --recalculate --notify')
//     ->dailyAt('06:00')
//     ->withoutOverlapping()
//     ->runInBackground();

// Weekly on Monday: generate audit export for all companies (optional)
// Schedule::command('halal:export {company_id}')
//     ->weeklyOn(1, '07:00');