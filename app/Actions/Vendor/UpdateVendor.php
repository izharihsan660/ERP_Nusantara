<?php

namespace App\Actions\Vendor;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\Vendor;
use Illuminate\Http\Request;

class UpdateVendor
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(Vendor $vendor, array $data, Request $request): Vendor
    {
        $vendor->update($data);

        $this->recordActivity->handle('Vendor ubah', $vendor, "Mengubah vendor {$vendor->nama_vendor}", $request);

        return $vendor->refresh();
    }
}
