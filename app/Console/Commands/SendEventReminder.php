<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    protected $signature = 'event:send-reminders';
    protected $description = 'Gửi email nhắc nhở cho những người tham gia và tác giả của sự kiện diễn ra vào ngày mai';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        $events = Event::whereDate('start_time', $tomorrow)->get();

        foreach ($events as $event) {
            foreach ($event->participants as $participant) {
                Mail::to($participant->email)
                    ->send(new EventReminderMail($event, $participant));
            }
            // if ($event->author) {
            //     Mail::to($event->author->email)
            //         ->send(new EventReminderMail($event, $event->author));
            // }
        }

        $this->info('Đã gửi email nhắc nhở cho các sự kiện diễn ra vào ngày mai.');
    }
}
