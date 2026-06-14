<?php

namespace App\Services;

use App\Actions\Role\CreateRole;
use App\Actions\Role\DeleteRole;
use App\Actions\Role\UpdateRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(
        private readonly CreateRole $createRole,
        private readonly UpdateRole $updateRole,
        private readonly DeleteRole $deleteRole,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return Role::query()
            ->withCount('users')
            ->with('permissions:id,name')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function permissionGroups(): array
    {
        return Permission::query()
            ->orderBy('name')
            ->get(['name'])
            ->groupBy(fn (Permission $permission) => str($permission->name)->before(' ')->toString())
            ->map(fn ($permissions, string $module) => [
                'module' => $module,
                'permissions' => $permissions->pluck('name')->values(),
            ])
            ->values()
            ->all();
    }

    public function create(array $data, Request $request): Role
    {
        return $this->createRole->handle($data, $request);
    }

    public function update(Role $role, array $data, Request $request): Role
    {
        return $this->updateRole->handle($role, $data, $request);
    }

    public function delete(Role $role, Request $request): void
    {
        $this->deleteRole->handle($role, $request);
    }
}
