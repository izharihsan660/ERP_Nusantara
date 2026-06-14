<?php

namespace App\Actions\Katalog;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\Katalog;
use Illuminate\Http\Request;

class UpdateKatalog
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(Katalog $katalog, array $data, Request $request): Katalog
    {
        $katalog->update($data);

        $this->recordActivity->handle('Katalog ubah', $katalog, "Mengubah barang {$katalog->part_no}", $request);

        return $katalog->refresh();
    }
}
