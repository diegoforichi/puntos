<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('campanas:procesar-programadas')->everyFifteenMinutes()->onOneServer();
        $schedule->command('tenant:tareas-diarias')->daily()->at('03:00');
        $schedule->command('tenant:compactar-sqlite')->weeklyOn(1, '04:00')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
