<?php

namespace App\Actions\Katalog;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\Katalog;
use Illuminate\Http\Request;

class DeactivateKatalog
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(Katalog $katalog, Request $request): Katalog
    {
        $katalog->update(['is_active' => false]);

        $this->recordActivity->handle('deactivated_katalog', $katalog, "Menonaktifkan barang {$katalog->part_no}", $request);

        return $katalog;
    }
}
