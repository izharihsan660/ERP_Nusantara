<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class NotificationHelper
{
    /**
     * @return Collection<int, User>
     */
    public function getUsersByRole(string $roleName): Collection
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', [$roleName, 'Superadmin']))
            ->where('is_active', true)
            ->get()
            ->unique('id')
            ->values();
    }
}
