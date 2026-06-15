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
        'Katalog' => ['lihat', 'tambah', 'ubah', 'hapus', 'import'],
        'Customer' => ['lihat', 'tambah', 'ubah', 'hapus'],
        'Vendor' => ['lihat', 'tambah', 'ubah', 'hapus'],
        'Site' => ['lihat', 'tambah', 'ubah', 'hapus'],
        'Template Dokumen' => ['lihat', 'tambah', 'ubah', 'hapus'],
        'Jabatan' => ['lihat', 'tambah', 'ubah', 'hapus'],
        'User' => ['lihat', 'tambah', 'ubah', 'hapus'],
    ];

    /**
     * @var array<int, string>
     */
    private array $reportPermissions = [
        'laporan_rekapan_po',
        'laporan_rekapan_wip',
        'laporan_rekapan_spb',
        'laporan_rekapan_invoice',
        'laporan_rekapan_pd',
        'laporan_profit',
        'laporan_outstanding',
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

    /**
     * @var array<int, string>
     */
    private array $spbPermissions = [
        'lihat_spb',
        'buat_spb',
        'download_pdf_spb',
        'void_spb',
    ];

    /**
     * @var array<int, string>
     */
    private array $invoicePermissions = [
        'lihat_invoice',
        'buat_invoice',
        'upload_ttd_invoice',
        'update_pembayaran_invoice',
        'void_invoice',
    ];

    /**
     * @var array<int, string>
     */
    private array $permintaanDanaPermissions = [
        'lihat_pd',
        'buat_pd',
        'approve_pd',
        'upload_bukti_pd',
        'void_pd',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = collect($this->permissions)
            ->flatMap(fn (array $actions, string $module) => collect($actions)->map(fn (string $action) => "{$module} {$action}"))
            ->merge($this->salesOrderPermissions)
            ->merge($this->purchaseOrderPermissions)
            ->merge($this->spbPermissions)
            ->merge($this->invoicePermissions)
            ->merge($this->permintaanDanaPermissions)
            ->merge($this->reportPermissions)
            ->values();

        $permissionNames->each(fn (string $name) => Permission::findOrCreate($name, 'web'));

        $roles = [
            'Superadmin' => $permissionNames->all(),
            'Sales' => [
                ...$this->onlyModules(['Quotation', 'WIP']),
                ...$this->salesOrderPermissions,
                'lihat_purchase_order',
                'buat_purchase_order',
                'lihat_spb',
                'lihat_invoice',
                'laporan_rekapan_po',
                'laporan_profit',
            ],
            'Gudang' => [
                ...$this->onlyModules(['SPB']),
                'lihat_spb',
                'buat_spb',
                'download_pdf_spb',
                'void_spb',
                'laporan_rekapan_wip',
                'laporan_rekapan_spb',
            ],
            'Finance' => [
                ...$this->onlyModules(['Invoice/Nota']),
                ...$this->invoicePermissions,
                'laporan_rekapan_invoice',
                'laporan_outstanding',
            ],
            'Procurement' => [
                'lihat_pd',
                'buat_pd',
                'upload_bukti_pd',
                'laporan_rekapan_pd',
            ],
            'Manager' => [
                ...$this->onlyActions(['approve']),
                'lihat_pd',
                'approve_pd',
                'lihat_purchase_order',
                'approve_purchase_order',
                'lihat_spb',
                'download_pdf_spb',
                'lihat_invoice',
                ...$this->reportPermissions,
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
