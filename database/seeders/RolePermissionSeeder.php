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
        'quotation' => ['lihat_quotation', 'buat_quotation', 'approve_quotation', 'download_pdf_quotation', 'void_quotation'],
        'wip' => ['lihat_wip', 'buat_wip', 'update_status_wip', 'void_wip'],
        'surat_kuasa' => ['lihat_surat_kuasa', 'buat_surat_kuasa', 'download_pdf_surat_kuasa'],
        'katalog' => ['lihat_katalog', 'tambah_katalog', 'ubah_katalog', 'hapus_katalog', 'import_katalog'],
        'customer' => ['lihat_customer', 'tambah_customer', 'ubah_customer', 'hapus_customer'],
        'vendor' => ['lihat_vendor', 'tambah_vendor', 'ubah_vendor', 'hapus_vendor'],
        'site' => ['lihat_site', 'tambah_site', 'ubah_site', 'hapus_site'],
        'template' => ['lihat_template', 'tambah_template', 'ubah_template', 'hapus_template'],
        'jabatan' => ['lihat_jabatan', 'tambah_jabatan', 'ubah_jabatan', 'hapus_jabatan'],
        'user' => ['lihat_user', 'tambah_user', 'ubah_user', 'hapus_user'],
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
            ->flatten()
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
                'lihat_quotation',
                'buat_quotation',
                'download_pdf_quotation',
                'void_quotation',
                ...$this->onlyModules(['wip']),
                ...$this->salesOrderPermissions,
                'lihat_purchase_order',
                'buat_purchase_order',
                'lihat_spb',
                'lihat_invoice',
                'laporan_rekapan_po',
                'laporan_profit',
            ],
            'Gudang' => [
                ...$this->spbPermissions,
                'lihat_spb',
                'buat_spb',
                'download_pdf_spb',
                'void_spb',
                'laporan_rekapan_wip',
                'laporan_rekapan_spb',
            ],
            'Finance' => [
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
                'approve_quotation',
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
            ->flatten()
            ->values()
            ->all();
    }
}
