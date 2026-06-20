<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Enums\VendorType;
use App\Models\Customer;
use App\Models\DocumentTemplate;
use App\Models\Katalog;
use App\Models\Site;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $templates = $this->seedDocumentTemplates();
        $customers = $this->seedCustomers($templates);

        $this->seedVendors();
        $this->seedSites($customers);
        $this->seedKatalog();
    }

    /**
     * @return array<string, DocumentTemplate>
     */
    private function seedDocumentTemplates(): array
    {
        $templates = [
            'default_quotation' => [
                'nama_template' => 'Default Quotation',
                'tipe_dokumen' => DocumentType::Quotation,
                'blade_file' => 'pdf.quotation.default',
                'is_default' => true,
            ],
            'mil_quotation' => [
                'nama_template' => 'MIL Quotation',
                'tipe_dokumen' => DocumentType::Quotation,
                'blade_file' => 'pdf.quotation.mil',
                'is_default' => false,
            ],
            'default_spb' => [
                'nama_template' => 'Default SPB',
                'tipe_dokumen' => DocumentType::Spb,
                'blade_file' => 'pdf.spb.default',
                'is_default' => true,
            ],
            'mil_spb' => [
                'nama_template' => 'MIL SPB',
                'tipe_dokumen' => DocumentType::Spb,
                'blade_file' => 'pdf.spb.mil',
                'is_default' => false,
            ],
            'default_invoice' => [
                'nama_template' => 'Default Invoice',
                'tipe_dokumen' => DocumentType::Invoice,
                'blade_file' => 'pdf.invoice.invoice',
                'is_default' => true,
            ],
            'default_nota' => [
                'nama_template' => 'Default Nota',
                'tipe_dokumen' => DocumentType::Nota,
                'blade_file' => 'pdf.invoice.nota',
                'is_default' => true,
            ],
            'default_po_naj' => [
                'nama_template' => 'Default PO NAJ',
                'tipe_dokumen' => DocumentType::PoNaj,
                'blade_file' => 'pdf.purchase-order.default',
                'is_default' => true,
            ],
        ];

        return collect($templates)
            ->map(fn (array $template, string $code): DocumentTemplate => DocumentTemplate::updateOrCreate(
                ['kode_template' => $code],
                [
                    'nama_template' => $template['nama_template'],
                    'tipe_dokumen' => $template['tipe_dokumen'],
                    'blade_file' => $template['blade_file'],
                    'is_default' => $template['is_default'],
                    'keterangan' => null,
                ],
            ))
            ->all();
    }

    /**
     * @param  array<string, DocumentTemplate>  $templates
     * @return array<string, Customer>
     */
    private function seedCustomers(array $templates): array
    {
        $customers = [
            'MIL' => 'PT. Mitra Indah Lestari',
            'KMSI' => 'PT. Kaltim Methanol Industri',
            'PKT' => 'PT. Pupuk Kaltim',
            'PHE' => 'PT. Pertamina Hulu',
            'ST' => 'CV. Sarana Teknik',
        ];

        return collect($customers)
            ->map(function (string $name, string $code) use ($templates): Customer {
                $isMil = $code === 'MIL';

                return Customer::updateOrCreate(
                    ['kode_customer' => $code],
                    [
                        'nama_customer' => $name,
                        'kota' => $code === 'ST' ? 'Makassar' : null,
                        'template_quotation_id' => $isMil ? $templates['mil_quotation']->id : $templates['default_quotation']->id,
                        'template_spb_id' => $isMil ? $templates['mil_spb']->id : $templates['default_spb']->id,
                        'is_active' => true,
                    ],
                );
            })
            ->all();
    }

    private function seedVendors(): void
    {
        $vendors = [
            ['nama_vendor' => 'RMA Indonesia', 'tipe_vendor' => VendorType::Rma],
            ['nama_vendor' => 'CV. Pallet Nusantara', 'tipe_vendor' => VendorType::VendorLain],
            ['nama_vendor' => 'PT. Logistik Borneo', 'tipe_vendor' => VendorType::VendorLain],
        ];

        foreach ($vendors as $vendor) {
            Vendor::updateOrCreate(
                ['nama_vendor' => $vendor['nama_vendor']],
                ['tipe_vendor' => $vendor['tipe_vendor']],
            );
        }
    }

    /**
     * @param  array<string, Customer>  $customers
     */
    private function seedSites(array $customers): void
    {
        $sites = [
            'MIL' => ['Site Balikpapan', 'Site Samarinda'],
            'KMSI' => ['Site Bontang', 'Site Balikpapan'],
            'PKT' => ['Site Bontang'],
            'PHE' => ['Site Sangatta'],
            'ST' => ['Site Makassar'],
        ];

        foreach ($sites as $customerCode => $siteNames) {
            foreach ($siteNames as $siteName) {
                Site::updateOrCreate(
                    [
                        'customer_id' => $customers[$customerCode]->id,
                        'nama_site' => $siteName,
                    ],
                    ['alamat' => null],
                );
            }
        }
    }

    private function seedKatalog(): void
    {
        $items = [
            ['nama_barang' => 'Filter Oli Mahindra', 'part_no' => 'MH-FO-001', 'hpp' => 150000, 'harga_jual_default' => 225000, 'satuan' => 'PCS'],
            ['nama_barang' => 'Filter Solar Mahindra', 'part_no' => 'MH-FS-001', 'hpp' => 120000, 'harga_jual_default' => 180000, 'satuan' => 'PCS'],
            ['nama_barang' => 'Kampas Rem Depan', 'part_no' => 'MH-KR-001', 'hpp' => 250000, 'harga_jual_default' => 375000, 'satuan' => 'SET'],
            ['nama_barang' => 'Oli Mesin 10W40', 'part_no' => 'OL-10W40-4L', 'hpp' => 85000, 'harga_jual_default' => 130000, 'satuan' => 'LITER'],
            ['nama_barang' => 'V-Belt', 'part_no' => 'VB-001', 'hpp' => 75000, 'harga_jual_default' => 115000, 'satuan' => 'PCS'],
            ['nama_barang' => 'Busi Iridium', 'part_no' => 'BS-IR-001', 'hpp' => 45000, 'harga_jual_default' => 70000, 'satuan' => 'PCS'],
            ['nama_barang' => 'Air Filter', 'part_no' => 'AF-001', 'hpp' => 95000, 'harga_jual_default' => 145000, 'satuan' => 'PCS'],
            ['nama_barang' => 'Bearing Roda', 'part_no' => 'BR-001', 'hpp' => 185000, 'harga_jual_default' => 280000, 'satuan' => 'PCS'],
            ['nama_barang' => 'Shock Absorber', 'part_no' => 'SA-001', 'hpp' => 450000, 'harga_jual_default' => 675000, 'satuan' => 'PCS'],
            ['nama_barang' => 'Timing Belt Kit', 'part_no' => 'TB-KIT-001', 'hpp' => 380000, 'harga_jual_default' => 570000, 'satuan' => 'SET'],
        ];

        foreach ($items as $item) {
            Katalog::updateOrCreate(
                ['part_no' => $item['part_no']],
                $item + ['kategori' => 'Spare Part', 'is_active' => true],
            );
        }
    }
}
