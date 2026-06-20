<?php

namespace App\Actions\User;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\User;
use Illuminate\Http\Request;

class DeactivateUser
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(User $user, Request $request): User
    {
        abort_if($request->user()?->is($user), 422, 'User aktif tidak bisa menonaktifkan dirinya sendiri.');

        $user->update(['is_active' => false]);

        $this->recordActivity->handle('deactivated_user', $user, "Menonaktifkan user {$user->email}", $request);

        return $user;
    }
}
