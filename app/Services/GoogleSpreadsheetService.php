<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;

class GoogleSpreadsheetService
{
    protected Sheets $service;
    protected string $spreadsheetId;
    protected string $range;

    public function __construct()
    {
        $client = new Client();
        $client->setAuthConfig(
            config('services.google_sheets.service_account_json')
        );
        $client->addScope(Sheets::SPREADSHEETS);

        $this->service = new Sheets($client);
        $this->spreadsheetId = config('services.google_sheets.sheet_id');
        $this->range         = config('services.google_sheets.range');
    }

    public function generateReportTitle(): string
    {
        // Report October 7, 8:17 AM
        $title = 'Report ' . now()->format('F j, g:i A');
        $columnNames = [
            'ID',
            'Kaspi URL',
            'Demper Name',
            'Demper Price',
        ];
        $body = new Sheets\ValueRange([
            'values' => [
                [],
                [$title],
                $columnNames,    
            ]
        ]);

        $params = [
            'valueInputOption' => 'RAW'
        ];

        $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            $this->range,
            $body,
            $params
        );

        return $title;
    }

    public function appendRow(array $rowData): void
    {
        $body = new Sheets\ValueRange([
            'values' => [ $rowData ]
        ]);

        $params = [
            'valueInputOption' => 'RAW'
        ];

        $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            $this->range,
            $body,
            $params
        );
    }
}
