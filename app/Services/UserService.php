<?php

namespace App\Services;

use App\Actions\User\CreateUser;
use App\Actions\User\DeactivateUser;
use App\Actions\User\UpdateUser;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class UserService
{
    public function __construct(
        private readonly CreateUser $createUser,
        private readonly UpdateUser $updateUser,
        private readonly DeactivateUser $deactivateUser,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return User::query()
            ->with('roles:id,name')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when(($filters['status'] ?? 'all') !== 'all', fn ($query) => $query->where('is_active', $filters['status'] === 'active'))
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function create(array $data, Request $request): User
    {
        return $this->createUser->handle($data, $request);
    }

    public function update(User $user, array $data, Request $request): User
    {
        return $this->updateUser->handle($user, $data, $request);
    }

    public function deactivate(User $user, Request $request): User
    {
        return $this->deactivateUser->handle($user, $request);
    }
}
