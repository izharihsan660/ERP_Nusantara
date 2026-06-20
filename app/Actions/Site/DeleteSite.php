<?php

namespace App\Actions\Site;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\Site;
use Illuminate\Http\Request;

class DeleteSite
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(Site $site, Request $request): void
    {
        $this->recordActivity->handle('deleted_site', $site, "Menghapus site {$site->nama_site}", $request);

        $site->delete();
    }
}
