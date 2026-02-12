<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

app(Schedule::class)
    ->command('campanas:procesar-programadas')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->description('Procesa campañas programadas y las encola para envío');

app(Schedule::class)
    ->command('tenant:compactar-sqlite --analyze')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->withoutOverlapping()
    ->description('Compacta bases SQLite de todos los tenants (domingo 3 AM)');
