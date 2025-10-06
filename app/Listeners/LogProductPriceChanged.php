<?php

namespace App\Listeners;

use App\Events\ProductPriceChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogProductPriceChanged
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProductPriceChanged $event): void
    {
        Log::info('ProductPriceChanged', [
            'productId'     => $event->productId,
            'url'           => $event->url,
            'merchantName'  => $event->merchantName,
            'price'         => $event->price,
        ]);
    }
}
