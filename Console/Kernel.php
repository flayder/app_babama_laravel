<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\BlockIoIPN;
use App\Models\Gateway;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        BlockIoIPN::class,
    ];

    /** Define the application's command schedule. */
    protected function schedule(Schedule $schedule): void
    {
         $schedule->command('sync:orders')->everyFiveMinutes();
         $schedule->command('sync:services:soc-prof')->dailyAt('06:00');

        $blockIoGateway = Gateway::where(['code' => 'blockio', 'status' => 1])->count();
        if (1 == $blockIoGateway) {
            $schedule->command('blockIo:ipn')->everyThirtyMinutes();
        }
    }

    /** Register the commands for the application. */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
