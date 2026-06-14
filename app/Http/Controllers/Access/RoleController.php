<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Access/Roles/Index', [
            'roles' => $this->roleService->paginate($request->query()),
            'permissionGroups' => $this->roleService->permissionGroups(),
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Access/Roles/Form', [
            'role' => null,
            'permissionGroups' => $this->roleService->permissionGroups(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roleService->create($request->validated(), $request);

        return to_route('roles.index')->with('success', 'Jabatan berhasil dibuat.');
    }

    public function edit(Role $role): Response
    {
        $role->load('permissions:id,name');

        return Inertia::render('Access/Roles/Form', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values(),
            ],
            'permissionGroups' => $this->roleService->permissionGroups(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roleService->update($role, $request->validated(), $request);

        return to_route('roles.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()?->can('Jabatan hapus'), 403);

        $this->roleService->delete($role, $request);

        return back()->with('success', 'Jabatan berhasil dihapus.');
    }
}
