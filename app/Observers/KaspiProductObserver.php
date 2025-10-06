<?php

namespace App\Observers;

use App\Models\KaspiProduct;
use App\Services\KaspiProductService;   

class KaspiProductObserver
{
    public function __construct(private KaspiProductService $kaspi)
    {
    }

    public function created(KaspiProduct $product): void
    {
        $this->kaspi->getAuthorPrice($product);
    }

    /**
     * Handle the KaspiProduct "updated" event.
     */
    public function updated(KaspiProduct $kaspiProduct): void
    {
        //
    }

    /**
     * Handle the KaspiProduct "deleted" event.
     */
    public function deleted(KaspiProduct $kaspiProduct): void
    {
        //
    }

    /**
     * Handle the KaspiProduct "restored" event.
     */
    public function restored(KaspiProduct $kaspiProduct): void
    {
        //
    }

    /**
     * Handle the KaspiProduct "force deleted" event.
     */
    public function forceDeleted(KaspiProduct $kaspiProduct): void
    {
        //
    }
}
