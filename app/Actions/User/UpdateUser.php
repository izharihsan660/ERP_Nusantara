<?php

namespace App\Actions\User;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\User;
use Illuminate\Http\Request;

class UpdateUser
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(User $user, array $data, Request $request): User
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles($roles);

        $this->recordActivity->handle('updated_user', $user, "Mengubah user {$user->email}", $request);

        return $user->refresh();
    }
}
