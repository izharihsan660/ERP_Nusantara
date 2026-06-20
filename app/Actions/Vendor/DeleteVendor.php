<?php

namespace App\Actions\Vendor;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\Vendor;
use Illuminate\Http\Request;

class DeleteVendor
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(Vendor $vendor, Request $request): void
    {
        $this->recordActivity->handle('deleted_vendor', $vendor, "Menghapus vendor {$vendor->nama_vendor}", $request);

        $vendor->delete();
    }
}
