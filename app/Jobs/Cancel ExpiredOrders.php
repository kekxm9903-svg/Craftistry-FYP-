<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CancelExpiredOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Order::where('status', 'pending_payment')
            ->where('created_at', '<=', now()->subHours(24))
            ->with('items.artwork')
            ->each(function (Order $order) {
                foreach ($order->items as $item) {
                    if ($item->artwork) {
                        $item->artwork->increment('available_stock', $item->quantity);
                    }
                }
                $order->update(['status' => 'cancelled']);
            });
    }
}