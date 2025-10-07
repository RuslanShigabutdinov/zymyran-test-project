<?php

namespace App\Listeners;

use App\Events\ProductPriceChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Services\GoogleSpreadsheetService;


class AppendGoogleSheetsProductPriceChange
{
    protected GoogleSpreadsheetService $sheets;

    public function __construct(GoogleSpreadsheetService $sheets)
    {
        $this->sheets = $sheets;
    }

    public function handle(ProductPriceChanged $event): void
    {
        $this->sheets->appendRow([
            $event->productId,
            $event->url,
            $event->merchantName,
            $event->price,
        ]);
    }
}
