<?php

namespace Tests\Support;

use App\Enums\DocumentType;
use App\Enums\InvoiceStatus;
use App\Enums\MetodePembayaran;
use App\Enums\PDStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\SpbStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusSupply;
use App\Enums\TipeOrder;
use App\Enums\VendorType;
use App\Enums\WIPStatus;
use App\Models\Customer;
use App\Models\DocumentTemplate;
use App\Models\Invoice;
use App\Models\Katalog;
use App\Models\PermintaanDana;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\Site;
use App\Models\Spb;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WipOrder;
use App\Services\InvoicePDFService;
use App\Services\InvoiceService;
use App\Services\PermintaanDanaPDFService;
use App\Services\PermintaanDanaService;
use App\Services\PurchaseOrderPDFService;
use App\Services\PurchaseOrderService;
use App\Services\QuotationPDFService;
use App\Services\QuotationService;
use App\Services\SalesOrderService;
use App\Services\SpbPDFService;
use App\Services\SpbService;
use App\Services\WipOrderService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait CreatesErpFixtures
{
    protected function seedPermissions(): void
    {
        $this->seed(RolePermissionSeeder::class);
    }

    protected function actingAsRole(string $roleName): User
    {
        $this->seedPermissions();

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(Role::findByName($roleName));
        $this->actingAs($user);

        return $user;
    }

    protected function actingAsUserWithPermissions(array $permissions): User
    {
        $this->seedPermissions();

        $user = User::factory()->create(['is_active' => true]);

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user->givePermissionTo($permissions);
        $this->actingAs($user);

        return $user;
    }

    protected function mockGeneratedPdfs(): void
    {
        Storage::fake('local');

        $this->partialMock(QuotationPDFService::class, function ($mock): void {
            $mock->shouldReceive('generate')->andReturn('quotations/test.pdf')->byDefault();
            $mock->shouldReceive('path')->andReturn('quotations/test.pdf')->byDefault();
        });

        $this->partialMock(PurchaseOrderPDFService::class, function ($mock): void {
            $mock->shouldReceive('generate')->andReturn('purchase-orders/test.pdf')->byDefault();
            $mock->shouldReceive('path')->andReturn('purchase-orders/test.pdf')->byDefault();
        });

        $this->partialMock(PermintaanDanaPDFService::class, function ($mock): void {
            $mock->shouldReceive('generate')->andReturn('permintaan-dana/test.pdf')->byDefault();
            $mock->shouldReceive('path')->andReturn('permintaan-dana/test.pdf')->byDefault();
        });

        $this->partialMock(SpbPDFService::class, function ($mock): void {
            $mock->shouldReceive('generate')->andReturn('spb/test.pdf')->byDefault();
            $mock->shouldReceive('path')->andReturn('spb/test.pdf')->byDefault();
        });

        $this->partialMock(InvoicePDFService::class, function ($mock): void {
            $mock->shouldReceive('generateAll')->andReturn([])->byDefault();
        });
    }

    protected function putPdf(string $path): void
    {
        Storage::disk('local')->put($path, "%PDF-1.4\n% Test PDF\n");
    }

    protected function supportData(string $suffix = 'TEST'): array
    {
        $unique = strtolower($suffix).'-'.str()->random(8);

        $quotationTemplate = DocumentTemplate::query()->create([
            'kode_template' => 'QT-'.$unique,
            'nama_template' => 'Quotation '.$suffix,
            'tipe_dokumen' => DocumentType::Quotation,
            'blade_file' => 'pdf.quotation.default',
            'is_default' => true,
        ]);

        $spbTemplate = DocumentTemplate::query()->create([
            'kode_template' => 'SPB-'.$unique,
            'nama_template' => 'SPB '.$suffix,
            'tipe_dokumen' => DocumentType::Spb,
            'blade_file' => 'pdf.spb.default',
            'is_default' => true,
        ]);

        $customer = Customer::query()->create([
            'kode_customer' => 'CUST-'.$unique,
            'nama_customer' => 'Customer '.$suffix,
            'template_quotation_id' => $quotationTemplate->id,
            'template_spb_id' => $spbTemplate->id,
            'is_active' => true,
        ]);

        $site = Site::query()->create([
            'customer_id' => $customer->id,
            'nama_site' => 'Site '.$suffix,
            'alamat' => 'Makassar',
        ]);

        $katalog = Katalog::query()->create([
            'part_no' => 'PART-'.$unique,
            'nama_barang' => 'Barang '.$suffix,
            'satuan' => 'PCS',
            'hpp' => 100000,
            'harga_jual_default' => 150000,
            'is_active' => true,
        ]);

        $vendor = Vendor::query()->create([
            'tipe_vendor' => VendorType::VendorLain,
            'nama_vendor' => 'Vendor '.$suffix,
        ]);

        return compact('customer', 'site', 'katalog', 'vendor', 'quotationTemplate', 'spbTemplate');
    }

    protected function quotationPayload(array $data = [], array $overrides = []): array
    {
        return array_replace_recursive([
            'tgl_quotation' => '2026-06-15',
            'customer_id' => $data['customer']->id,
            'template_id' => $data['quotationTemplate']->id,
            'catatan' => 'Catatan quotation',
            'items' => [
                [
                    'katalog_id' => $data['katalog']->id,
                    'qty' => 2,
                    'harga_satuan' => 150000,
                ],
            ],
        ], $overrides);
    }

    protected function purchaseOrderPayload(array $data = [], array $overrides = []): array
    {
        return array_replace_recursive([
            'customer_id' => $data['customer']->id,
            'vendor_id' => $data['vendor']->id,
            'tgl_po' => '2026-06-15',
            'no_pr_customer' => 'PR-CUST-001',
            'no_po_customer' => null,
            'catatan' => 'Catatan PO',
            'items' => [
                [
                    'katalog_id' => $data['katalog']->id,
                    'deskripsi' => $data['katalog']->nama_barang,
                    'qty' => 2,
                    'satuan' => $data['katalog']->satuan,
                    'harga_satuan' => 125000,
                ],
            ],
        ], $overrides);
    }

    protected function spbPayload(array $data = [], array $overrides = []): array
    {
        return array_replace_recursive([
            'tgl_spb' => '2026-06-15',
            'customer_id' => null,
            'site_id' => $data['site']->id,
            'nama_ekspedisi' => 'JNE Trucking',
            'etd' => '2026-06-16',
            'eta' => '2026-06-17',
            'catatan' => 'Catatan SPB',
            'items' => [
                [
                    'part_no' => $data['katalog']->part_no,
                    'deskripsi' => $data['katalog']->nama_barang,
                    'qty' => 1,
                    'berat' => 1.5,
                    'volume' => 0.5,
                    'dimensi' => '10x10x10',
                    'sku' => 'SKU-'.$data['katalog']->id,
                ],
            ],
        ], $overrides);
    }

    protected function permintaanDanaPayload(array $overrides = []): array
    {
        return array_replace([
            'tujuan' => 'Operasional Kantor',
            'rekening_tujuan' => '1234567890',
            'bank_tujuan' => 'BCA',
            'plan_pembayaran' => '2026-06-15',
            'keterangan' => 'Biaya operasional kantor',
            'referensi_dokumen' => 'REF-PD-001',
            'items' => [
                [
                    'no_part' => 'OPS-001',
                    'description' => 'Biaya operasional kantor',
                    'qty' => 1,
                    'harga' => 2500000,
                    'remarks' => null,
                ],
            ],
        ], $overrides);
    }

    protected function createQuotation(array $data, array $overrides = []): Quotation
    {
        $user = User::query()->first() ?? User::factory()->create(['is_active' => true]);
        $quotation = app(QuotationService::class)->create($this->quotationPayload($data, $overrides), $user);

        return $quotation->refresh()->load('items');
    }

    protected function createApprovedQuotation(array $data): Quotation
    {
        $user = User::query()->first() ?? User::factory()->create(['is_active' => true]);
        $quotation = $this->createQuotation($data);
        app(QuotationService::class)->submit($quotation, $user);

        return app(QuotationService::class)->approve($quotation->refresh(), $user);
    }

    protected function createSalesOrder(Quotation $quotation, array $overrides = []): SalesOrder
    {
        $user = User::query()->first() ?? User::factory()->create(['is_active' => true]);

        return app(SalesOrderService::class)->create(array_replace([
            'no_po_customer' => 'PO-CUST-001',
            'no_pr_customer' => 'PR-CUST-001',
            'tgl_po' => '2026-06-15',
            'metode_pembayaran' => MetodePembayaran::TOP->value,
            'top_hari' => 30,
        ], $overrides), $quotation, $user);
    }

    protected function createWipOrder(SalesOrder $salesOrder, array $overrides = []): WipOrder
    {
        $user = User::query()->first() ?? User::factory()->create(['is_active' => true]);
        $quotationItem = $salesOrder->quotation->items()->firstOrFail();

        return app(WipOrderService::class)->create(array_replace([
            'no_wip' => 'WIP-001',
            'tipe_order' => TipeOrder::VOR->value,
            'nama_ekspedisi' => 'JNE Trucking',
            'items' => [
                [
                    'katalog_id' => $quotationItem->katalog_id,
                    'part_no' => $quotationItem->part_no,
                    'qty' => $quotationItem->qty,
                ],
            ],
        ], $overrides), $salesOrder, $user);
    }

    protected function createPurchaseOrder(array $data, array $overrides = []): PurchaseOrder
    {
        $user = User::query()->first() ?? User::factory()->create(['is_active' => true]);

        return app(PurchaseOrderService::class)->create($this->purchaseOrderPayload($data, $overrides), $user);
    }

    protected function createApprovedPurchaseOrder(array $data, array $overrides = []): PurchaseOrder
    {
        $user = User::query()->first() ?? User::factory()->create(['is_active' => true]);
        $purchaseOrder = $this->createPurchaseOrder($data, $overrides);
        app(PurchaseOrderService::class)->submit($purchaseOrder, $user);

        return app(PurchaseOrderService::class)->approve($purchaseOrder->refresh(), $user);
    }

    protected function createSpbFromWip(WipOrder $wip, array $data, array $overrides = []): Spb
    {
        $user = User::query()->first() ?? User::factory()->create(['is_active' => true]);

        return app(SpbService::class)->create($this->spbPayload($data, $overrides), $wip, $user);
    }

    protected function createSpbFromPurchaseOrder(PurchaseOrder $purchaseOrder, array $data, array $overrides = []): Spb
    {
        $user = User::query()->first() ?? User::factory()->create(['is_active' => true]);

        return app(SpbService::class)->create($this->spbPayload($data, $overrides), $purchaseOrder, $user);
    }

    protected function createInvoice(Spb $spb, array $overrides = []): Invoice
    {
        $user = User::query()->first() ?? User::factory()->create(['is_active' => true]);

        return app(InvoiceService::class)->create(array_replace([
            'tgl_dokumen' => '2026-06-15',
            'metode_pembayaran' => MetodePembayaran::TOP->value,
            'top_hari' => 30,
            'no_faktur_pajak' => 'FP-001',
        ], $overrides), $spb, $user);
    }

    protected function createPermintaanDana(array $overrides = []): PermintaanDana
    {
        $user = User::query()->first() ?? User::factory()->create(['is_active' => true]);

        return app(PermintaanDanaService::class)->create($this->permintaanDanaPayload($overrides), $user);
    }

    protected function createPersistedSpb(array $data, array $overrides = []): Spb
    {
        return Spb::query()->create(array_replace([
            'no_spb' => '001/WHMKS/NAJ/VI/26',
            'tgl_spb' => '2026-06-15',
            'customer_id' => $data['customer']->id,
            'site_id' => $data['site']->id,
            'template_id' => $data['spbTemplate']->id,
            'spb_able_type' => WipOrder::class,
            'spb_able_id' => 1,
            'referensi_tipe' => 'PO',
            'no_referensi' => 'PO-CUST-001',
            'nama_ekspedisi' => 'JNE Trucking',
            'status' => SpbStatus::Shipped,
            'created_by' => User::query()->first()?->id ?? User::factory()->create(['is_active' => true])->id,
        ], $overrides));
    }

    protected function createPersistedInvoice(array $data, array $overrides = []): Invoice
    {
        $spb = $overrides['spb'] ?? $this->createPersistedSpb($data);
        unset($overrides['spb']);

        return Invoice::query()->create(array_replace([
            'no_dokumen' => '001/NOTA-NAJ/MKS/NAJGROUP/VI/2026',
            'tipe_dokumen' => 'INVOICE',
            'tgl_dokumen' => '2026-06-15',
            'spb_id' => $spb->id,
            'customer_id' => $data['customer']->id,
            'total_nilai' => 300000,
            'total_hpp' => 200000,
            'total_profit' => 100000,
            'metode_pembayaran' => 'TOP',
            'top_hari' => 30,
            'tgl_jatuh_tempo' => '2026-07-15',
            'status_pembayaran' => StatusPembayaran::Belum,
            'jumlah_bayar' => 0,
            'status' => InvoiceStatus::Active,
            'created_by' => User::query()->first()?->id ?? User::factory()->create(['is_active' => true])->id,
        ], $overrides));
    }

    protected function createPersistedWip(SalesOrder $salesOrder, array $overrides = []): WipOrder
    {
        return WipOrder::query()->create(array_replace([
            'sales_order_id' => $salesOrder->id,
            'no_wip' => 'WIP-PERSISTED',
            'tipe_order' => TipeOrder::VOR,
            'nama_ekspedisi' => 'JNE Trucking',
            'status_supply' => StatusSupply::BelumTersupply,
            'status' => WIPStatus::Active,
            'created_by' => User::query()->first()?->id ?? User::factory()->create(['is_active' => true])->id,
        ], $overrides));
    }

    protected function createPersistedSalesOrder(Quotation $quotation, array $overrides = []): SalesOrder
    {
        return SalesOrder::query()->create(array_replace([
            'quotation_id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'no_po_customer' => 'PO-PERSISTED',
            'no_pr_customer' => 'PR-PERSISTED',
            'tgl_po' => '2026-06-15',
            'metode_pembayaran' => MetodePembayaran::TOP,
            'top_hari' => 30,
            'status' => SalesOrderStatus::Open,
            'created_by' => User::query()->first()?->id ?? User::factory()->create(['is_active' => true])->id,
        ], $overrides));
    }

    protected function createPersistedPurchaseOrder(array $data, array $overrides = []): PurchaseOrder
    {
        return PurchaseOrder::query()->create(array_replace([
            'no_purchase_order' => '001/PO-NAJ/VI/2026',
            'tgl_po' => '2026-06-15',
            'customer_id' => $data['customer']->id,
            'vendor_id' => $data['vendor']->id,
            'no_pr_customer' => 'PR-CUST-001',
            'no_po_customer' => null,
            'status' => PurchaseOrderStatus::Draft,
            'created_by' => User::query()->first()?->id ?? User::factory()->create(['is_active' => true])->id,
        ], $overrides));
    }

    protected function createPersistedPermintaanDana(array $overrides = []): PermintaanDana
    {
        $permintaanDana = PermintaanDana::query()->create(array_replace([
            'no_pd' => '001/PD-NAJ/VI/2026',
            'tujuan' => 'Operasional Kantor',
            'rekening_tujuan' => '1234567890',
            'bank_tujuan' => 'BCA',
            'plan_pembayaran' => '2026-06-15',
            'keterangan' => 'Biaya operasional kantor',
            'status' => PDStatus::Draft,
            'created_by' => User::query()->first()?->id ?? User::factory()->create(['is_active' => true])->id,
        ], $overrides));

        $permintaanDana->items()->create([
            'no_part' => 'OPS-001',
            'description' => 'Biaya operasional kantor',
            'qty' => 1,
            'harga' => 2500000,
            'total' => 2500000,
        ]);

        return $permintaanDana;
    }
}
