<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_default_uses_smtp_when_database_host_is_configured(): void
    {
        AppSetting::query()->create([
            'key' => 'mail_host',
            'value' => 'smtp.example.test',
            'label' => 'SMTP Host',
            'group' => 'email',
        ]);

        config(['mail.default' => 'log']);
        Cache::forget('app_settings_mail');

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame('smtp', config('mail.default'));
    }

    public function test_mail_default_remains_environment_value_without_database_settings(): void
    {
        $environmentMailer = config('mail.default');
        Cache::forget('app_settings_mail');

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame($environmentMailer, config('mail.default'));
    }

    public function test_graph_mailer_is_not_overridden_by_database_smtp_settings(): void
    {
        AppSetting::query()->create([
            'key' => 'mail_host',
            'value' => 'smtp.example.test',
            'label' => 'SMTP Host',
            'group' => 'email',
        ]);

        config(['mail.default' => 'graph']);
        Cache::forget('app_settings_mail');

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame('graph', config('mail.default'));
    }
}
