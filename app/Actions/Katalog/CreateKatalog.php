<?php

namespace App\Actions\Katalog;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\Katalog;
use Illuminate\Http\Request;

class CreateKatalog
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(array $data, Request $request): Katalog
    {
        $katalog = Katalog::create($data);

        $this->recordActivity->handle('created_katalog', $katalog, "Menambah barang {$katalog->part_no}", $request);

        return $katalog;
    }
}
