<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $permissionMap = [
        'lihat_po_customer' => 'lihat_sales_order',
        'input_po_customer' => 'input_sales_order',
        'void_po_customer' => 'void_sales_order',
        'PO Customer lihat' => 'lihat_sales_order',
        'PO Customer input' => 'input_sales_order',
        'PO Customer void' => 'void_sales_order',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        DB::transaction(function () {
            foreach ($this->permissionMap as $oldName => $newName) {
                $this->renamePermission($oldName, $newName);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        DB::transaction(function () {
            $this->renamePermission('lihat_sales_order', 'lihat_po_customer');
            $this->renamePermission('input_sales_order', 'input_po_customer');
            $this->renamePermission('void_sales_order', 'void_po_customer');
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function renamePermission(string $oldName, string $newName): void
    {
        $oldPermission = DB::table('permissions')
            ->where('name', $oldName)
            ->where('guard_name', 'web')
            ->first(['id']);

        if (! $oldPermission) {
            return;
        }

        $newPermission = DB::table('permissions')
            ->where('name', $newName)
            ->where('guard_name', 'web')
            ->first(['id']);

        if (! $newPermission) {
            DB::table('permissions')
                ->where('id', $oldPermission->id)
                ->update(['name' => $newName]);

            return;
        }

        $this->movePermissionRelations((int) $oldPermission->id, (int) $newPermission->id);

        DB::table('permissions')
            ->where('id', $oldPermission->id)
            ->delete();
    }

    private function movePermissionRelations(int $oldPermissionId, int $newPermissionId): void
    {
        if (Schema::hasTable('role_has_permissions')) {
            DB::table('role_has_permissions')
                ->where('permission_id', $oldPermissionId)
                ->orderBy('role_id')
                ->each(function (object $row) use ($newPermissionId) {
                    DB::table('role_has_permissions')->updateOrInsert([
                        'permission_id' => $newPermissionId,
                        'role_id' => $row->role_id,
                    ]);
                });

            DB::table('role_has_permissions')
                ->where('permission_id', $oldPermissionId)
                ->delete();
        }

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')
                ->where('permission_id', $oldPermissionId)
                ->orderBy('model_id')
                ->each(function (object $row) use ($newPermissionId) {
                    DB::table('model_has_permissions')->updateOrInsert([
                        'permission_id' => $newPermissionId,
                        'model_type' => $row->model_type,
                        'model_id' => $row->model_id,
                    ]);
                });

            DB::table('model_has_permissions')
                ->where('permission_id', $oldPermissionId)
                ->delete();
        }
    }
};
