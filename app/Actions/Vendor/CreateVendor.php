<?php

namespace App\Actions\Vendor;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\Vendor;
use Illuminate\Http\Request;

class CreateVendor
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(array $data, Request $request): Vendor
    {
        $vendor = Vendor::create($data);

        $this->recordActivity->handle('created_vendor', $vendor, "Menambah vendor {$vendor->nama_vendor}", $request);

        return $vendor;
    }
}
