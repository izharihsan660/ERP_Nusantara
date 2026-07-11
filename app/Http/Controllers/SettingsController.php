<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\TestEmailSettingsRequest;
use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settingsService) {}

    public function index(): Response
    {
        return Inertia::render('Settings/Index', [
            'settings' => $this->settingsService->groupedSettings(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $this->settingsService->update($request->validated('settings'));

        return back()->with('success', 'Konfigurasi berhasil disimpan.');
    }

    public function testEmail(TestEmailSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->settingsService->sendTestEmail($validated['email']);

            return back()->with('success', 'Email test berhasil dikirim ke '.$validated['email']);
        } catch (\Throwable $e) {
            Log::error($e, ['action' => 'test_email', 'user_id' => $request->user()?->id]);

            return back()->with('error', 'Gagal mengirim email, periksa konfigurasi SMTP.');
        }
    }
}
