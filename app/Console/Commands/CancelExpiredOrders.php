<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CancelExpiredOrders extends Command
{
    protected $signature   = 'orders:cancel-expired';
    protected $description = 'Auto-cancel orders stuck in pending_payment for more than 24 hours';

    public function handle()
    {
        $expiredOrders = Order::where('status', 'pending_payment')
            ->where('created_at', '<', Carbon::now()->subHours(24))
            ->get();

        $count = 0;

        foreach ($expiredOrders as $order) {
            $order->update([
                'status'              => 'cancelled',
                'cancellation_reason' => 'Payment not received within 24 hours. Order automatically cancelled.',
            ]);

            $count++;
            $this->line("Cancelled order #{$order->id} (created {$order->created_at})");
        }

        $this->info("Done. {$count} order(s) cancelled.");

        return Command::SUCCESS;
    }
}