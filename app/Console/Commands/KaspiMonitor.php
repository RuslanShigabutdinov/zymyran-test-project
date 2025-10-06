<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\KaspiProduct;
use App\Services\KaspiProductService;

class KaspiMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:kaspi-monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check each KaspiProduct entry to find all products that have lower prices';

    /**
     * Execute the console command.
     */
    public function handle(KaspiProductService $service) {
        $items = KaspiProduct::all();
        foreach($items as $item) {
            $this->info("Checking product #{$item->id}");
            $service->checkProductPrices($item);
            usleep(250_000);
        }
        $this->info('Checked all products');
    }
}
