<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'signature_url' => $request->user()->signature_path
                ? route('profile.signature.show', ['t' => $request->user()->updated_at?->timestamp])
                : null,
        ]);
    }

    public function signature(Request $request): BinaryFileResponse
    {
        $signaturePath = $request->user()->signature_path;

        abort_if(blank($signaturePath) || ! Storage::disk('local')->exists($signaturePath), 404);

        return response()->file(Storage::disk('local')->path($signaturePath));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Update the user's digital signature.
     */
    public function updateSignature(Request $request): RedirectResponse
    {
        $request->validate([
            'signature' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $user = $request->user();
        $path = "signatures/{$user->id}.png";

        if ($user->signature_path) {
            Storage::delete($user->signature_path);
        }

        Storage::makeDirectory('signatures');

        $manager = new ImageManager(new Driver);
        $manager
            ->decodePath($request->file('signature')->getPathname())
            ->scaleDown(width: 400, height: 200)
            ->save(Storage::path($path));

        $user->forceFill([
            'signature_path' => $path,
        ])->save();

        return Redirect::route('profile.edit')->with('status', 'signature-updated');
    }

    /**
     * Delete the user's digital signature.
     */
    public function deleteSignature(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->signature_path) {
            Storage::delete($user->signature_path);
        }

        $user->forceFill([
            'signature_path' => null,
        ])->save();

        return Redirect::route('profile.edit')->with('status', 'signature-deleted');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
