<?php

namespace App\Actions\User;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\User;
use Illuminate\Http\Request;

class CreateUser
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(array $data, Request $request): User
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $user = User::create($data);
        $user->syncRoles($roles);

        $this->recordActivity->handle('created_user', $user, "Menambah user {$user->email}", $request);

        return $user;
    }
}
