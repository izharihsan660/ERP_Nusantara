<?php

namespace App\Actions\Site;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\Site;
use Illuminate\Http\Request;

class CreateSite
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(array $data, Request $request): Site
    {
        $site = Site::create($data);

        $this->recordActivity->handle('Site tambah', $site, "Menambah site {$site->nama_site}", $request);

        return $site;
    }
}
