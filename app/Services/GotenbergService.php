<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GotenbergService
{
    private string $url;

    public function __construct()
    {
        $this->url = rtrim((string) config('services.gotenberg.url'), '/');
    }

    public function convertDocxToPdf(string $docxPath): string
    {
        $response = Http::timeout(30)
            ->attach('files', file_get_contents($docxPath), basename($docxPath))
            ->post("{$this->url}/forms/libreoffice/convert");

        if (! $response->successful()) {
            throw new \RuntimeException(
                "Gotenberg convert failed: HTTP {$response->status()} — {$response->body()}"
            );
        }

        return $response->body();
    }
}
