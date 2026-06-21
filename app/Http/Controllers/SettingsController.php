<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        $settings = AppSetting::query()->get()->groupBy('group');

        return Inertia::render('Settings/Index', [
            'settings' => $settings->map(fn ($group) => $group->map(fn ($setting) => [
                'key' => $setting->key,
                'value' => $setting->key === 'mail_password' ? '********' : $setting->value,
                'label' => $setting->label,
            ])),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'exists:app_settings,key'],
            'settings.*.value' => ['nullable', 'string'],
        ]);

        foreach ($validated['settings'] as $item) {
            $value = $item['value'] ?? null;

            if ($item['key'] === 'mail_password' && filled($value) && $value !== '********') {
                $value = Crypt::encryptString($value);
            }

            AppSetting::query()->where('key', $item['key'])->update(['value' => $value]);
        }

        Artisan::call('config:clear');

        return back()->with('success', 'Konfigurasi berhasil disimpan.');
    }

    public function testEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            Mail::raw('Ini adalah email test dari ERP PT. Nusantara Abadi Jaya.', function ($message) use ($validated) {
                $message->to($validated['email'])
                    ->subject('Test Email - ERP NAJ');
            });

            return back()->with('success', 'Email test berhasil dikirim ke '.$validated['email']);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim email: '.$e->getMessage());
        }
    }
}
