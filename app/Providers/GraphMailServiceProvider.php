<?php

namespace App\Providers;

use App\Mail\Transport\MicrosoftGraphTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class GraphMailServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Mail::extend('graph', function (array $config = []) {
            return new MicrosoftGraphTransport(
                tenantId: (string) ($config['tenant_id'] ?? ''),
                clientId: (string) ($config['client_id'] ?? ''),
                clientSecret: (string) ($config['client_secret'] ?? ''),
                sender: (string) ($config['sender'] ?? ''),
            );
        });
    }
}
