<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Sync Dialogflow intents every 6 hours
        $schedule->command('dialogflow:sync-intents')
            ->everySixHours()
            ->withoutOverlapping()
            ->runInBackground();

        // Daily maintenance at 2 AM
        $schedule->command('chatbot:maintenance --clean-data=90 --optimize')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Weekly full maintenance (including intent sync)
        $schedule->command('chatbot:maintenance --sync-intents --clean-data=90 --optimize')
            ->weeklyOn(1, '03:00') // Monday at 3 AM
            ->withoutOverlapping()
            ->runInBackground();

        // Clean extremely old data monthly
        $schedule->call(function () {
            \App\Services\ChatbotAnalyticsService::cleanOldData(180); // 6 months
        })->monthlyOn(1, '04:00'); // First day of month at 4 AM

        // Generate analytics reports weekly
        $schedule->call(function () {
            $data = \App\Services\ChatbotAnalyticsService::getDashboardData(7);
            \Illuminate\Support\Facades\Log::info('Weekly Chatbot Analytics', $data);
        })->weeklyOn(1, '05:00'); // Monday at 5 AM
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
