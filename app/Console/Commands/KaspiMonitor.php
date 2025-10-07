<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\KaspiProduct;
use App\Services\KaspiProductService;
use App\Services\GoogleSpreadsheetService;

class KaspiMonitor extends Command
{
    protected $signature = 'app:kaspi-monitor';

    protected $description = 'Check each KaspiProduct entry to find all products that have lower prices';

    public function handle(
        KaspiProductService $service,
        GoogleSpreadsheetService $sheets
        ) {
        $items = KaspiProduct::all();
        if($items)
            $sheets->generateReportTitle();
        foreach($items as $item) {
            $this->info("Checking product #{$item->id}");
            $service->checkProductPrices($item);
            usleep(250_000);
        }
        $this->info('Checked all products');
    }
}
