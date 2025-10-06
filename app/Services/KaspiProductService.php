<?php

namespace App\Services;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Events\ProductPriceChanged;
use App\Models\KaspiProduct;


class KaspiProductService
{

    private int $cityId;
    private int $productId;
    private Client $http;
    private CookieJar $jar;
    private string $companyName;

    public function __construct() {
        $this->jar = new CookieJar();
        $this->http = new Client([
            'base_uri'    => 'https://kaspi.kz',
            'cookies'     => $this->jar,
            'timeout'     => 10,
            'http_errors' => false,
            'headers'     => [
                'User-Agent'      => 'Mozilla/5.0 (X11; Linux x86_64; rv:143.0) Gecko/20100101 Firefox/143.0',
                'Accept'          => 'application/json, text/*',
                'Accept-Language' => 'ru,en;q=0.9',
            ],
        ]);
        $this->companyName = config('app.company_name');
    }

    public function getAuthorPrice(KaspiProduct $kaspiProduct): void {
        $url = $kaspiProduct->product_url;

        $this->setCityId($url);
        $this->setProductId($url);

        $this->setupRequest($url);

        $limit = 10;
        $page  = 0;
        $total = null;
        $parsedCount = 0;

        do {
            $payload = [
                'cityId'         => (string)$this->cityId,
                'id'             => (string)$this->productId,
                'limit'          => $limit,
                'page'           => $page,
                'sortOption'     => 'PRICE',
                'installationId' => '-1',
            ];

            $resp = $this->postOffers($this->productId, $payload, $url);

            if ($resp['status'] === 403) {
                Log::warning("Kaspi offers HTTP {$resp['status']} for product {$this->productId}: {$resp['body']}");
                $this->setupRequest($url);
                $resp = $this->postOffers($this->productId, $payload, $url);
            }

            if ($resp['status'] !== 200) {
                Log::warning("Kaspi offers HTTP {$resp['status']} for product {$this->productId}: {$resp['body']}");
                break;
            }

            $json  = json_decode($resp['body'], true);
            $chunk = $json['offers'] ?? [];
            $total = $json['offersCount'] ?? null;

            foreach ($chunk as $item) {
                if($item['merchantName'] == $this->companyName) {
                    $authorPrice = (int) $item['price'];
                    $kaspiProduct->update([
                        'author_price' => $authorPrice,
                    ]);
                    Log::info('Price has been found', [
                        'url' => $url,
                        'author_price' => $authorPrice,
                    ]);
                    return;
                }
            }

            $parsedCount += count($chunk);

            usleep(250_000);

            $page++;
        } while ($chunk !== [] && $total > $parsedCount);
        
        Log::warning("Author product isnt found", [
            'productId'   => $this->productId,
            'cityId'      => $this->cityId,
            'total'       => $total,
            'parsedCount' => $parsedCount,
            'url'         => $url,
        ]);
    }

    public function checkProductPrices(KaspiProduct $kaspiProduct): void {
        $url = $kaspiProduct->product_url;

        $this->setCityId($url);
        $this->setProductId($url);

        $this->setupRequest($url);

        $limit = 10;
        $page  = 0;
        $total = null;
        $parsedCount = 0;

        $lowestPrice = $kaspiProduct->author_price;
        $companyName = null;

        $dumpedProducts = [];

        do {
            $payload = [
                'cityId'         => (string)$this->cityId,
                'id'             => (string)$this->productId,
                'limit'          => $limit,
                'page'           => $page,
                'sortOption'     => 'PRICE',
                'installationId' => '-1',
            ];

            $resp = $this->postOffers($this->productId, $payload, $url);

            if ($resp['status'] === 403) {
                Log::warning("Kaspi offers HTTP {$resp['status']} for product {$this->productId}: {$resp['body']}");
                $this->setupRequest($url);
                $resp = $this->postOffers($this->productId, $payload, $url);
            }

            if ($resp['status'] !== 200) {
                Log::warning("Kaspi offers HTTP {$resp['status']} for product {$this->productId}: {$resp['body']}");
                break;
            }

            $json  = json_decode($resp['body'], true);
            $chunk = $json['offers'] ?? [];
            $total = $json['offersCount'] ?? null;

            foreach ($chunk as $item) {
                if($item['price'] < $lowestPrice ) {
                    $companyName = $item['merchantName'];
                    $lowestPrice = $item['price'];
                }
            }

            $parsedCount += count($chunk);

            usleep(250_000);
            
            $page++;
        } while ($chunk !== [] && $total < $parsedCount);
        if($companyName !== null) {
            ProductPriceChanged::dispatch(
                $kaspiProduct->id,
                $url,
                $companyName,
                $lowestPrice
            );
        }
        else {
            Log::info("Monitor statistic: ", [
                'productId' => $this->productId,
                'isDumped'  => false,
                'total'     => $total,
                'parsedCount' => $parsedCount,
            ]);
        }
        $kaspiProduct->update([
            'last_checked_at' => Carbon::now(),
        ]);
    }

    private function setupRequest(string $productUrl): void {
        $this->http->get($this->relative($productUrl), [
            'headers' => [
                'Accept'    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Referer'   => $productUrl,
                'X-KS-City' => $this->cityId,
            ]
        ]);
    }

    private function postOffers(string $productId, array $payload, string $referer): array {
        $res = $this->http->post("/yml/offer-view/offers/{$productId}", [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Referer'      => $referer,
                'Origin'       => 'https://kaspi.kz',
            ],
            'json' => $payload,
        ]);
        return [
            'status' => $res->getStatusCode(),
            'body'   => (string) $res->getBody(),
        ];
    }

    private function setCityId(string $url) {
        $this->cityId = explode('?c=', $url)[1];
    }

    private function setProductId(string $url) {
        // from https://kaspi.kz/shop/p/samsung-galaxy-a06-6-gb-128-gb-chernyi-123429834/?c=750000000
        // to https://kaspi.kz/shop/p/samsung-galaxy-a06-6-gb-128-gb-chernyi-123429834
        $linkWithoutCity = explode('/?c=', $url)[0];
        // get last element of "-"
        $parts = explode('-', $linkWithoutCity);
        $this->productId = end($parts);
    }

    private function relative(string $url): string
    {
        // если уже относительный — вернём как есть
        if (str_starts_with($url, '/')) return $url;

        $parts = parse_url($url);
        $path  = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? ('?'.$parts['query']) : '';
        return $path.$query;
    }

}
