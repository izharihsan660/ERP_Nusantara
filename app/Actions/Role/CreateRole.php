<?php

namespace App\Actions\Role;

use App\Actions\ActivityLog\RecordActivity;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class CreateRole
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(array $data, Request $request): Role
    {
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        $this->recordActivity->handle('created_role', $role, "Menambah jabatan {$role->name}", $request);

        return $role;
    }
}
