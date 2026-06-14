<?php

namespace App\Actions\Site;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\Site;
use Illuminate\Http\Request;

class UpdateSite
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(Site $site, array $data, Request $request): Site
    {
        $site->update($data);

        $this->recordActivity->handle('Site ubah', $site, "Mengubah site {$site->nama_site}", $request);

        return $site->refresh();
    }
}
