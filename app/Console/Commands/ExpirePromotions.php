<?php

namespace App\Console\Commands;

use App\Models\ArtworkSell;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpirePromotions extends Command
{
    protected $signature   = 'promotions:expire';
    protected $description = 'Disable promotions that have passed their end date';

    public function handle()
    {
        $count = ArtworkSell::where('promotion_enabled', 1)
            ->whereNotNull('promotion_ends_at')
            ->where('promotion_ends_at', '<', Carbon::now())
            ->update([
                'promotion_enabled'   => 0,
                'promotion_discount'  => null,
                'promotion_starts_at' => null,
                'promotion_ends_at'   => null,
            ]);

        $this->info("Expired {$count} promotion(s).");
        return Command::SUCCESS;
    }
}