<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\EncryptPatientFields::class,
        \App\Console\Commands\EncryptExistingPatientData::class,
        \App\Console\Commands\AutoSaveMonthlyReport::class,
        \App\Console\Commands\AutoSaveQuarterlyReport::class,
        \App\Console\Commands\SendVaccinationReminders::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Check if auto-save is enabled
        if (!config('auto_save.enabled', true)) {
            return;
        }
        
        // Auto-save monthly report on the 1st day of each month at 1:00 AM
        // This saves the previous month's report
        if (config('auto_save.monthly.enabled', true)) {
            $schedule->command('reports:auto-save-monthly')
                ->monthlyOn(1, '01:00')
                ->timezone(config('auto_save.monthly.timezone', 'Asia/Manila'))
                ->withoutOverlapping()
                ->onSuccess(function () {
                    \Log::info('Monthly vaccination report auto-saved successfully');
                })
                ->onFailure(function () {
                    \Log::error('Failed to auto-save monthly vaccination report');
                });
        }
        
        // Auto-save quarterly report on the first day after each quarter ends
        // Q1 (Jan-Mar): Runs on April 1 at 2:00 AM
        // Q2 (Apr-Jun): Runs on July 1 at 2:00 AM
        // Q3 (Jul-Sep): Runs on October 1 at 2:00 AM
        // Q4 (Oct-Dec): Runs on January 1 at 2:00 AM
        if (config('auto_save.quarterly.enabled', true)) {
            $schedule->command('reports:auto-save-quarterly')
                ->cron('0 2 1 1,4,7,10 *') // January, April, July, October at 2:00 AM
                ->timezone(config('auto_save.quarterly.timezone', 'Asia/Manila'))
                ->withoutOverlapping()
                ->onSuccess(function () {
                    \Log::info('Quarterly vaccination report auto-saved successfully');
                })
                ->onFailure(function () {
                    \Log::error('Failed to auto-save quarterly vaccination report');
                });
        }
        
        // Send vaccination reminders daily at 6:00 AM
        // Sends reminders for schedules that are 3 days away or 1 day away
        $schedule->command('notifications:send-vaccination-reminders')
            ->dailyAt('06:00')
            ->timezone('Asia/Manila')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Log::info('Vaccination reminders sent successfully');
            })
            ->onFailure(function () {
                \Log::error('Failed to send vaccination reminders');
            });
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}