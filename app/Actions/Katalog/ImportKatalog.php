<?php

namespace App\Actions\Katalog;

use App\Actions\ActivityLog\RecordActivity;
use App\Imports\KatalogImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportKatalog
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(string $path, Request $request): void
    {
        Excel::import(new KatalogImport, $path);

        $this->recordActivity->handle('imported_katalog', null, 'Import katalog dari Excel', $request);
    }
}
