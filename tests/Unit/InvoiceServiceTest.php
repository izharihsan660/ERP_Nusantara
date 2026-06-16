<?php

namespace Tests\Unit;

use App\Enums\MetodePembayaran;
use App\Enums\StatusPembayaran;
use App\Enums\TipeDokumen;
use App\Models\Spb;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use CreatesErpFixtures;
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockGeneratedPdfs();
        $this->user = User::factory()->create(['is_active' => true]);
    }

    public function test_create_top_uses_invoice_type_and_calculates_due_date(): void
    {
        $spb = $this->preparedSpb('INV-TOP');

        $invoice = $this->createInvoice($spb, [
            'metode_pembayaran' => MetodePembayaran::TOP->value,
            'top_hari' => 30,
        ]);

        $this->assertSame(TipeDokumen::Invoice, $invoice->tipe_dokumen);
        $this->assertSame('2026-07-15', $invoice->tgl_jatuh_tempo?->format('Y-m-d'));
    }

    public function test_create_cod_and_cbd_use_nota_penjualan_type(): void
    {
        $cod = $this->createInvoice($this->preparedSpb('INV-COD'), [
            'metode_pembayaran' => MetodePembayaran::COD->value,
            'top_hari' => null,
        ]);
        $cbd = $this->createInvoice($this->preparedSpb('INV-CBD'), [
            'metode_pembayaran' => MetodePembayaran::CBD->value,
            'top_hari' => null,
        ]);

        $this->assertSame(TipeDokumen::NotaPenjualan, $cod->tipe_dokumen);
        $this->assertSame(TipeDokumen::NotaPenjualan, $cbd->tipe_dokumen);
    }

    public function test_create_fails_when_spb_already_has_active_invoice(): void
    {
        $spb = $this->preparedSpb('INV-DUP');
        $this->createInvoice($spb);

        $this->expectException(ValidationException::class);
        $this->createInvoice($spb->refresh());
    }

    public function test_update_pembayaran_sets_partial_and_paid_statuses(): void
    {
        $invoice = $this->createInvoice($this->preparedSpb('INV-PAY'));

        $partial = app(InvoiceService::class)->updatePembayaran($invoice, [
            'tgl_bayar' => '2026-06-20',
            'jumlah_bayar' => 1,
        ], $this->user);
        $this->assertSame(StatusPembayaran::Sebagian, $partial->status_pembayaran);

        $paid = app(InvoiceService::class)->updatePembayaran($partial, [
            'tgl_bayar' => '2026-06-21',
            'jumlah_bayar' => $partial->total_nilai,
        ], $this->user);
        $this->assertSame(StatusPembayaran::Lunas, $paid->status_pembayaran);
    }

    public function test_void_fails_when_invoice_has_payment(): void
    {
        $invoice = $this->createInvoice($this->preparedSpb('INV-VOID'));
        app(InvoiceService::class)->updatePembayaran($invoice, [
            'tgl_bayar' => '2026-06-20',
            'jumlah_bayar' => 1,
        ], $this->user);

        $this->expectException(ValidationException::class);
        app(InvoiceService::class)->void($invoice->refresh(), 'Sudah dibayar.', $this->user);
    }

    private function preparedSpb(string $suffix): Spb
    {
        $data = $this->supportData($suffix);
        $quotation = $this->createApprovedQuotation($data);
        $salesOrder = $this->createSalesOrder($quotation);
        $wip = $this->createWipOrder($salesOrder);

        return $this->createSpbFromWip($wip, $data);
    }
}
