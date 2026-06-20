<?php

namespace App\Actions\Role;

use App\Actions\ActivityLog\RecordActivity;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UpdateRole
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(Role $role, array $data, Request $request): Role
    {
        abort_if($role->name === 'Superadmin' && $data['name'] !== 'Superadmin', 422, 'Nama jabatan Superadmin tidak boleh diubah.');

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        $this->recordActivity->handle('updated_role', $role, "Mengubah jabatan {$role->name}", $request);

        return $role->refresh();
    }
}
