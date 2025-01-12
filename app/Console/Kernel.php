<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Event;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventReminder;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $events = Event::where('start_time', '>=', now()->subMinutes(10))
                ->where('start_time', '<', now()->addMinutes(10))
                ->get();

            foreach ($events as $event) {
                foreach ($event->favorites as $favorite) {
                    Mail::to($favorite->user->email)->send(new EventReminder($event));
                }
            }
        })->everyMinute();
    }


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
