<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Notification;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendClassReminders extends Command
{
    protected $signature   = 'notifications:class-reminders';
    protected $description = 'Send reminders to buyers enrolled in classes starting in 3 days';

    public function handle(): void
    {
        $targetDate = Carbon::now()->addDays(3)->toDateString();

        $this->info("Checking for classes starting on: {$targetDate}");

        $bookings = Booking::with(['classEvent', 'user'])
            ->whereHas('classEvent', function ($q) use ($targetDate) {
                $q->whereDate('start_date', $targetDate);
            })
            ->whereIn('payment_status', ['paid', 'free'])
            ->get();

        $this->info("Found {$bookings->count()} bookings for that date.");

        $count = 0;

        foreach ($bookings as $booking) {
            $class = $booking->classEvent;
            $user  = $booking->user;

            if (!$class || !$user) continue;

            // Avoid duplicate — skip if already sent today for this class + user
            $alreadySent = Notification::where('user_id', $user->id)
                ->where('type', 'class_reminder')
                ->where('url', route('class.event.show', $class->id))
                ->whereDate('created_at', today())
                ->exists();

            if ($alreadySent) {
                $this->line("  Skipped (already sent today): {$user->fullname} → {$class->title}");
                continue;
            }

            NotificationService::classReminder(
                $user->id,
                $class->id,
                $class->title,
                Carbon::parse($class->start_date)->format('d M Y')
            );

            $this->line("  Sent: {$user->fullname} → {$class->title}");
            $count++;
        }

        $this->info("Done. Sent {$count} class reminder notifications.");
    }
}