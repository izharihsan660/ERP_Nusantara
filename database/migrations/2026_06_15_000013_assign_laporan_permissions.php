<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $rolePermissions = [
        'Sales' => ['laporan_rekapan_po', 'laporan_profit'],
        'Gudang' => ['laporan_rekapan_wip', 'laporan_rekapan_spb'],
        'Finance' => ['laporan_rekapan_invoice', 'laporan_outstanding'],
        'Procurement' => ['laporan_rekapan_pd'],
        'Manager' => ['laporan_rekapan_po', 'laporan_rekapan_wip', 'laporan_rekapan_spb', 'laporan_rekapan_invoice', 'laporan_rekapan_pd', 'laporan_profit', 'laporan_outstanding'],
        'Superadmin' => ['laporan_rekapan_po', 'laporan_rekapan_wip', 'laporan_rekapan_spb', 'laporan_rekapan_invoice', 'laporan_rekapan_pd', 'laporan_profit', 'laporan_outstanding'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
            return;
        }

        DB::transaction(function (): void {
            foreach (collect($this->rolePermissions)->flatten()->unique() as $permission) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => $permission, 'guard_name' => 'web'],
                    ['created_at' => now(), 'updated_at' => now()],
                );
            }

            foreach ($this->rolePermissions as $roleName => $permissions) {
                $role = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->first(['id']);

                if (! $role) {
                    continue;
                }

                foreach ($permissions as $permissionName) {
                    $permission = DB::table('permissions')->where('name', $permissionName)->where('guard_name', 'web')->first(['id']);

                    if (! $permission) {
                        continue;
                    }

                    DB::table('role_has_permissions')->updateOrInsert([
                        'permission_id' => $permission->id,
                        'role_id' => $role->id,
                    ]);
                }
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        DB::table('permissions')
            ->whereIn('name', collect($this->rolePermissions)->flatten()->unique()->values()->all())
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
