<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $permissions = [
        'Quotation' => ['lihat', 'buat', 'approve', 'download_pdf', 'void'],
        'WIP' => ['lihat', 'buat', 'update_status', 'void'],
        'Surat Kuasa' => ['lihat', 'buat', 'download_pdf'],
        'PO NAJ' => ['lihat', 'buat', 'approve', 'download_pdf', 'void'],
        'SPB' => ['lihat', 'buat', 'download_pdf', 'void'],
        'Invoice/Nota' => ['lihat', 'buat', 'upload_ttd', 'update_pembayaran', 'void'],
        'PD' => ['lihat', 'buat', 'approve', 'upload_bukti', 'void'],
        'Katalog' => ['lihat', 'tambah', 'ubah', 'hapus', 'import'],
        'Customer' => ['lihat', 'tambah', 'ubah', 'hapus'],
        'Vendor' => ['lihat', 'tambah', 'ubah', 'hapus'],
        'Site' => ['lihat', 'tambah', 'ubah', 'hapus'],
        'Template Dokumen' => ['lihat', 'tambah', 'ubah', 'hapus'],
        'Jabatan' => ['lihat', 'tambah', 'ubah', 'hapus'],
        'User' => ['lihat', 'tambah', 'ubah', 'hapus'],
        'Laporan' => ['rekapan_po', 'rekapan_wip', 'rekapan_spb', 'rekapan_invoice', 'rekapan_pd', 'profit', 'outstanding'],
    ];

    /**
     * @var array<int, string>
     */
    private array $salesOrderPermissions = [
        'lihat_sales_order',
        'input_sales_order',
        'void_sales_order',
    ];

    /**
     * @var array<int, string>
     */
    private array $purchaseOrderPermissions = [
        'lihat_purchase_order',
        'buat_purchase_order',
        'approve_purchase_order',
        'download_pdf_purchase_order',
        'void_purchase_order',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = collect($this->permissions)
            ->flatMap(fn (array $actions, string $module) => collect($actions)->map(fn (string $action) => "{$module} {$action}"))
            ->merge($this->salesOrderPermissions)
            ->merge($this->purchaseOrderPermissions)
            ->values();

        $permissionNames->each(fn (string $name) => Permission::findOrCreate($name, 'web'));

        $roles = [
            'Superadmin' => $permissionNames->all(),
            'Sales' => [
                ...$this->onlyModules(['Quotation', 'WIP']),
                ...$this->salesOrderPermissions,
                'lihat_purchase_order',
                'buat_purchase_order',
            ],
            'Gudang' => $this->onlyModules(['SPB']),
            'Finance' => $this->onlyModules(['Invoice/Nota']),
            'Procurement' => $this->onlyModules(['PD']),
            'Manager' => [
                ...$this->onlyActions(['approve']),
                'lihat_purchase_order',
                'approve_purchase_order',
                ...$this->onlyModules(['Laporan']),
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        }
    }

    /**
     * @param  array<int, string>  $modules
     * @return array<int, string>
     */
    private function onlyModules(array $modules): array
    {
        return collect($this->permissions)
            ->only($modules)
            ->flatMap(fn (array $actions, string $module) => collect($actions)->map(fn (string $action) => "{$module} {$action}"))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $actions
     * @return array<int, string>
     */
    private function onlyActions(array $actions): array
    {
        return collect($this->permissions)
            ->flatMap(fn (array $moduleActions, string $module) => collect($moduleActions)
                ->filter(fn (string $action) => in_array($action, $actions, true))
                ->map(fn (string $action) => "{$module} {$action}"))
            ->values()
            ->all();
    }
}
