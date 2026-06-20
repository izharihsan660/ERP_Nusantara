<?php

namespace App\Actions\Role;

use App\Actions\ActivityLog\RecordActivity;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class DeleteRole
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(Role $role, Request $request): void
    {
        abort_if($role->name === 'Superadmin', 422, 'Jabatan Superadmin tidak boleh dihapus.');

        $this->recordActivity->handle('deleted_role', $role, "Menghapus jabatan {$role->name}", $request);

        $role->delete();
    }
}
