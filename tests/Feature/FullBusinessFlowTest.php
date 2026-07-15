<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Enums\InvoiceStatus;
use App\Enums\MetodePembayaran;
use App\Enums\PdDocumentKategori;
use App\Enums\PDStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\QuotationStatus;
use App\Enums\ReferensiTipe;
use App\Enums\SalesOrderStatus;
use App\Enums\SpbStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusSupply;
use App\Enums\TipeDokumen;
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
use App\Services\DocumentNumberService;
use App\Services\PermintaanDanaPDFService;
use App\Services\PurchaseOrderPDFService;
use App\Services\QuotationPDFService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FullBusinessFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partialMock(QuotationPDFService::class, function ($mock): void {
            $mock->shouldReceive('generate')->andReturnUsing(function (): string {
                Storage::disk('local')->put('quotations/test.pdf', "%PDF-1.4\n% Test PDF\n");

                return 'quotations/test.pdf';
            })->byDefault();
            $mock->shouldReceive('path')->andReturn('quotations/test.pdf')->byDefault();
        });
    }

    public function test_quotation_to_payment_flow_runs_end_to_end(): void
    {
        $user = $this->actingAsSuperadmin();
        $data = $this->supportData('QUOT');

        $this->get(route('quotations.create'))->assertOk();

        $this->post(route('quotations.store'), [
            'tgl_quotation' => '2026-06-15',
            'customer_id' => $data['customer']->id,
            'template_id' => $data['quotationTemplate']->id,
            'items' => [
                [
                    'katalog_id' => $data['katalog']->id,
                    'qty' => 2,
                    'harga_satuan' => 150000,
                ],
            ],
        ])->assertRedirect();

        $quotation = Quotation::query()->latest('id')->firstOrFail();
        $this->assertSame(QuotationStatus::Draft, $quotation->status);
        $this->assertMatchesRegularExpression('/^\d{3}\/QUOT\/NAJ-MKS\/VI\/26$/', $quotation->no_quotation);
        $this->assertEquals(300000, (float) $quotation->total);
        $this->assertEquals(100000, (float) $quotation->total_profit);

        $this->post(route('quotations.submit', $quotation))->assertRedirect();
        $this->assertSame(QuotationStatus::PendingApproval, $quotation->refresh()->status);

        $this->post(route('quotations.approve', $quotation))->assertRedirect();
        $quotation->refresh();
        $this->assertSame(QuotationStatus::Approved, $quotation->status);
        $this->assertNotEmpty($quotation->qr_token);
        Storage::disk('local')->assertExists(app(QuotationPDFService::class)->path($quotation));
        $this->get(route('verify.quotation', $quotation->qr_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('valid', true)
                ->where('document.nomor', $quotation->no_quotation));

        $this->post(route('quotations.sales-orders.store', $quotation), [
            'no_po_customer' => 'PO-CUST-QUOT',
            'no_pr_customer' => 'PR-CUST-QUOT',
            'tgl_po' => '2026-06-15',
            'metode_pembayaran' => MetodePembayaran::TOP->value,
            'top_hari' => 30,
        ])->assertRedirect();

        $salesOrder = SalesOrder::query()->where('quotation_id', $quotation->id)->firstOrFail();
        $this->assertSame(SalesOrderStatus::Open, $salesOrder->status);
        $this->assertSame(30, $salesOrder->top_hari);

        $this->post(route('sales-orders.wip-orders.store', $salesOrder), [
            'no_wip' => 'WIP-QUOT-001',
            'tipe_order' => TipeOrder::VOR->value,
            'nama_ekspedisi' => 'JNE Trucking',
            'items' => [
                [
                    'katalog_id' => $data['katalog']->id,
                    'part_no' => $data['katalog']->part_no,
                    'qty' => 2,
                ],
            ],
        ])->assertRedirect();

        $wip = $salesOrder->wipOrders()->firstOrFail();
        $this->assertSame(WIPStatus::Active, $wip->status);
        $this->assertSame(StatusSupply::BelumTersupply, $wip->status_supply);

        $this->post(route('wip-orders.spb.store', $wip), $this->spbPayload($data['site']->id, $data['katalog'], 'PR-CUST-QUOT'))
            ->assertRedirect();

        $spb = Spb::query()->where('spb_able_type', $wip::class)->where('spb_able_id', $wip->id)->firstOrFail();
        $this->assertSame(SpbStatus::Shipped, $spb->status);
        $this->assertSame(ReferensiTipe::PO, $spb->referensi_tipe);
        $this->assertSame('PO-CUST-QUOT', $spb->no_referensi);
        $this->assertSame(StatusSupply::Tersupply, $wip->refresh()->status_supply);
        Storage::disk('local')->assertExists('spb/'.$spb->id.'-'.str($spb->no_spb)->replace('/', '-')->slug('-').'.pdf');

        $this->post(route('spb.invoices.store', $spb), [
            'tgl_dokumen' => '2026-06-15',
            'metode_pembayaran' => MetodePembayaran::TOP->value,
            'top_hari' => 30,
            'no_faktur_pajak' => 'FP-QUOT-001',
        ])->assertRedirect();

        $invoice = Invoice::query()->where('spb_id', $spb->id)->firstOrFail();
        $this->assertSame(TipeDokumen::Invoice, $invoice->tipe_dokumen);
        $this->assertSame('2026-07-15', $invoice->tgl_jatuh_tempo?->format('Y-m-d'));
        Storage::disk('local')->assertExists("invoices/{$invoice->id}/invoice-".str($invoice->no_dokumen)->replace('/', '-')->slug('-').'.pdf');
        Storage::disk('local')->assertExists("invoices/{$invoice->id}/faktur-".str($invoice->no_dokumen)->replace('/', '-')->slug('-').'.pdf');
        Storage::disk('local')->assertExists("invoices/{$invoice->id}/tanda-terima-".str($invoice->no_dokumen)->replace('/', '-')->slug('-').'.pdf');

        $this->post(route('invoices.upload-ttd', $invoice), [
            'file_spb' => UploadedFile::fake()->image('spb.png'),
            'file_invoice' => UploadedFile::fake()->image('invoice.png'),
            'file_tanda_terima' => UploadedFile::fake()->image('tanda-terima.png'),
        ])->assertRedirect();
        $this->assertNotEmpty($invoice->refresh()->file_ttd_gabungan);
        Storage::disk('local')->assertExists($invoice->file_ttd_gabungan);

        $this->post(route('invoices.pembayaran', $invoice), [
            'tgl_bayar' => '2026-06-20',
            'jumlah_bayar' => 75000,
        ])->assertRedirect();
        $this->assertSame(StatusPembayaran::Sebagian, $invoice->refresh()->status_pembayaran);

        $this->post(route('invoices.pembayaran', $invoice), [
            'tgl_bayar' => '2026-06-21',
            'jumlah_bayar' => (float) $invoice->grand_total - (float) $invoice->jumlah_bayar,
        ])->assertRedirect();
        $this->assertSame(StatusPembayaran::Lunas, $invoice->refresh()->status_pembayaran);
    }

    public function test_purchase_order_to_invoice_flow_runs_end_to_end(): void
    {
        $this->actingAsSuperadmin();
        $data = $this->supportData('PO');

        $this->get(route('purchase-orders.create'))->assertOk();

        $this->post(route('purchase-orders.store'), [
            'customer_id' => $data['customer']->id,
            'vendor_id' => $data['vendor']->id,
            'tgl_po' => '2026-06-15',
            'no_pr_customer' => 'PR-CUST-PO',
            'no_po_customer' => null,
            'items' => [
                [
                    'katalog_id' => $data['katalog']->id,
                    'deskripsi' => $data['katalog']->nama_barang,
                    'qty' => 3,
                    'satuan' => $data['katalog']->satuan,
                    'harga_satuan' => 125000,
                ],
            ],
        ])->assertRedirect();

        $purchaseOrder = PurchaseOrder::query()->latest('id')->firstOrFail();
        $this->assertSame(PurchaseOrderStatus::Draft, $purchaseOrder->status);
        $this->assertMatchesRegularExpression('/^\d{3}\/PO-NAJ\/VI\/2026$/', $purchaseOrder->no_purchase_order);

        $this->post(route('purchase-orders.submit', $purchaseOrder))->assertRedirect();
        $this->assertSame(PurchaseOrderStatus::PendingApproval, $purchaseOrder->refresh()->status);

        $this->post(route('purchase-orders.approve', $purchaseOrder))->assertRedirect();
        $purchaseOrder->refresh();
        $this->assertSame(PurchaseOrderStatus::Approved, $purchaseOrder->status);
        $this->assertNotEmpty($purchaseOrder->qr_token);
        Storage::disk('local')->assertExists(app(PurchaseOrderPDFService::class)->path($purchaseOrder));
        $this->get(route('verify.quotation', $purchaseOrder->qr_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('valid', true)
                ->where('document.nomor', $purchaseOrder->no_purchase_order));

        $this->post(route('purchase-orders.spb.store', $purchaseOrder), $this->spbPayload($data['site']->id, $data['katalog'], 'PR-CUST-PO'))
            ->assertRedirect();

        $spb = Spb::query()->where('spb_able_type', $purchaseOrder::class)->where('spb_able_id', $purchaseOrder->id)->firstOrFail();
        $this->assertSame($data['customer']->id, $spb->customer_id);
        $this->assertSame(ReferensiTipe::PR, $spb->referensi_tipe);
        Storage::disk('local')->assertExists('spb/'.$spb->id.'-'.str($spb->no_spb)->replace('/', '-')->slug('-').'.pdf');

        $this->post(route('spb.invoices.store', $spb), [
            'tgl_dokumen' => '2026-06-15',
            'metode_pembayaran' => MetodePembayaran::COD->value,
            'top_hari' => null,
            'no_faktur_pajak' => 'FP-PO-001',
        ])->assertRedirect();

        $invoice = Invoice::query()->where('spb_id', $spb->id)->firstOrFail();
        $this->assertSame(TipeDokumen::NotaPenjualan, $invoice->tipe_dokumen);
        $this->assertSame(InvoiceStatus::Active, $invoice->status);
        $this->assertNull($invoice->tgl_jatuh_tempo);
    }

    public function test_permintaan_dana_approval_rejection_and_upload_flow_runs_end_to_end(): void
    {
        $this->travelTo('2026-06-15');
        $this->actingAsSuperadmin();

        $this->get(route('permintaan-dana.create'))->assertOk();

        $this->post(route('permintaan-dana.store'), [
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
                    'remarks' => 'UAT',
                ],
            ],
        ])->assertRedirect();

        $permintaanDana = PermintaanDana::query()->latest('id')->firstOrFail();
        $this->assertSame(PDStatus::Draft, $permintaanDana->status);
        $this->assertMatchesRegularExpression('/^\d{3}\/PD-NAJ\/VI\/2026$/', $permintaanDana->no_pd);

        $this->post(route('permintaan-dana.submit', $permintaanDana))->assertRedirect();
        $this->assertSame(PDStatus::PendingApproval, $permintaanDana->refresh()->status);

        $this->post(route('permintaan-dana.approve', $permintaanDana))->assertRedirect();
        $permintaanDana->refresh();
        $this->assertSame(PDStatus::Approved, $permintaanDana->status);
        $this->assertNotEmpty($permintaanDana->qr_token);
        Storage::disk('local')->assertExists(app(PermintaanDanaPDFService::class)->path($permintaanDana));
        $this->get(route('verify.quotation', $permintaanDana->qr_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('valid', true)
                ->where('document.nomor', $permintaanDana->no_pd));

        $this->post(route('permintaan-dana.upload-bukti', $permintaanDana), [
            'tgl_realisasi' => '2026-06-16',
            'jumlah_realisasi' => 2500000,
            'documents' => [
                [
                    'kategori' => PdDocumentKategori::BuktiPembelian->value,
                    'file' => UploadedFile::fake()->image('bukti.png'),
                ],
            ],
        ])->assertRedirect();
        $this->assertSame(PDStatus::Paid, $permintaanDana->refresh()->status);
        Storage::disk('local')->assertExists($permintaanDana->documents()->firstOrFail()->file_path);

        $rejected = PermintaanDana::query()->create([
            'no_pd' => app(DocumentNumberService::class)->generatePermintaanDanaNumber(now()),
            'tujuan' => 'PD untuk reject',
            'rekening_tujuan' => '1234567890',
            'bank_tujuan' => 'BCA',
            'plan_pembayaran' => '2026-06-15',
            'keterangan' => 'PD untuk reject',
            'status' => PDStatus::PendingApproval,
            'created_by' => auth()->id(),
        ]);
        $rejected->items()->create([
            'no_part' => 'REJ-001',
            'description' => 'PD untuk reject',
            'qty' => 1,
            'harga' => 100000,
            'total' => 100000,
        ]);

        $this->post(route('permintaan-dana.reject', $rejected), [
            'catatan_rejection' => 'Dokumen belum lengkap',
        ])->assertRedirect();
        $this->assertSame(PDStatus::Rejected, $rejected->refresh()->status);
        $this->assertSame('Dokumen belum lengkap', $rejected->catatan_rejection);
    }

    /**
     * @return array{customer: Customer, site: Site, katalog: Katalog, vendor: Vendor, quotationTemplate: DocumentTemplate, spbTemplate: DocumentTemplate}
     */
    private function supportData(string $suffix): array
    {
        $unique = strtolower($suffix).'-'.str()->random(8);

        $quotationTemplate = DocumentTemplate::query()->create([
            'kode_template' => 'QT-'.$unique,
            'nama_template' => 'Quotation Test '.$suffix,
            'tipe_dokumen' => DocumentType::Quotation,
            'blade_file' => 'pdf.quotation.default',
            'is_default' => true,
        ]);

        $spbTemplate = DocumentTemplate::query()->create([
            'kode_template' => 'SPB-'.$unique,
            'nama_template' => 'SPB Test '.$suffix,
            'tipe_dokumen' => DocumentType::Spb,
            'blade_file' => 'pdf.spb.default',
            'is_default' => true,
        ]);

        $customer = Customer::query()->create([
            'kode_customer' => 'CUST-'.$unique,
            'nama_customer' => 'Customer Test '.$suffix,
            'template_quotation_id' => $quotationTemplate->id,
            'template_spb_id' => $spbTemplate->id,
            'is_active' => true,
        ]);

        $site = Site::query()->create([
            'customer_id' => $customer->id,
            'nama_site' => 'Site Test '.$suffix,
            'alamat' => 'Makassar',
        ]);

        $katalog = Katalog::query()->create([
            'part_no' => 'PART-'.$unique,
            'nama_barang' => 'Barang Test '.$suffix,
            'satuan' => 'PCS',
            'hpp' => 100000,
            'harga_jual_default' => 150000,
            'is_active' => true,
        ]);

        $vendor = Vendor::query()->create([
            'tipe_vendor' => VendorType::VendorLain,
            'nama_vendor' => 'Vendor Test '.$suffix,
        ]);

        return compact('customer', 'site', 'katalog', 'vendor', 'quotationTemplate', 'spbTemplate');
    }

    private function actingAsSuperadmin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $user->assignRole(Role::findByName('Superadmin'));
        $this->actingAs($user);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function spbPayload(int $siteId, Katalog $katalog, string $fallbackReference): array
    {
        return [
            'tgl_spb' => '2026-06-15',
            'customer_id' => null,
            'site_id' => $siteId,
            'nama_ekspedisi' => 'JNE Trucking',
            'etd' => '2026-06-16',
            'eta' => '2026-06-17',
            'catatan' => $fallbackReference,
            'items' => [
                [
                    'part_no' => $katalog->part_no,
                    'deskripsi' => $katalog->nama_barang,
                    'qty' => 1,
                    'berat' => 1.5,
                    'volume' => 0.5,
                    'dimensi' => '10x10x10',
                    'sku' => 'SKU-'.$katalog->id,
                ],
            ],
        ];
    }
}
