<?php

namespace Tests\Feature;

use App\Enums\StatusPembayaran;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class InvoiceFeatureTest extends TestCase
{
    use CreatesErpFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockGeneratedPdfs();
    }

    public function test_invoice_create_payment_upload_ttd_and_void_validation(): void
    {
        $this->actingAsRole('Superadmin');
        $data = $this->supportData('INV-FEATURE');
        $spb = $this->createSpbFromWip(
            $this->createWipOrder($this->createSalesOrder($this->createApprovedQuotation($data))),
            $data,
        );

        $this->post(route('spb.invoices.store', $spb), [
            'tgl_dokumen' => '2026-06-15',
            'metode_pembayaran' => 'TOP',
            'top_hari' => 30,
            'no_faktur_pajak' => 'FP-FEATURE',
        ])->assertRedirect();

        $invoice = Invoice::query()->where('spb_id', $spb->id)->firstOrFail();
        $invoice->update(['total_nilai' => 1000000, 'ppn' => 110000, 'grand_total' => 1110000]);

        $this->post(route('invoices.pembayaran', $invoice), [
            'tgl_bayar' => '2026-06-20',
            'jumlah_bayar' => 300000,
        ])->assertRedirect();
        $this->assertSame(StatusPembayaran::Sebagian, $invoice->refresh()->status_pembayaran);
        $this->assertEquals(300000, (float) $invoice->jumlah_bayar);

        $this->post(route('invoices.pembayaran', $invoice), [
            'tgl_bayar' => '2026-06-21',
            'jumlah_bayar' => 900000,
        ])->assertSessionHasErrors('jumlah_bayar');
        $this->assertEquals(300000, (float) $invoice->refresh()->jumlah_bayar);

        $this->post(route('invoices.pembayaran', $invoice), [
            'tgl_bayar' => '2026-06-22',
            'jumlah_bayar' => 810000,
        ])->assertRedirect();
        $this->assertSame(StatusPembayaran::Lunas, $invoice->refresh()->status_pembayaran);
        $this->assertEquals(1110000, (float) $invoice->jumlah_bayar);

        $this->partialMock(InvoiceService::class, function ($mock): void {
            $mock->shouldReceive('uploadTtd')->andReturnUsing(function ($invoice) {
                $invoice->update(['file_ttd_gabungan' => 'ttd-gabungan/test.pdf']);

                return $invoice->refresh();
            });
        });

        $this->post(route('invoices.upload-ttd', $invoice), [
            'file_spb' => UploadedFile::fake()->image('spb.png'),
            'file_invoice' => UploadedFile::fake()->image('invoice.png'),
            'file_tanda_terima' => UploadedFile::fake()->image('tanda-terima.png'),
        ])->assertRedirect();
        $this->assertSame('ttd-gabungan/test.pdf', $invoice->refresh()->file_ttd_gabungan);

        $this->post(route('invoices.void', $invoice), ['alasan_void' => 'Sudah dibayar'])
            ->assertSessionHasErrors('status_pembayaran');
    }

    public function test_unmatched_spb_item_fails_invoice_creation_instead_of_using_zero_price(): void
    {
        $this->actingAsRole('Superadmin');
        $data = $this->supportData('INV-UNMATCHED');
        $spb = $this->createSpbFromWip(
            $this->createWipOrder($this->createSalesOrder($this->createApprovedQuotation($data))),
            $data,
        );
        $spb->items()->firstOrFail()->update(['part_no' => 'UNKNOWN', 'deskripsi' => 'Item tidak dikenal']);

        $this->post(route('spb.invoices.store', $spb), [
            'tgl_dokumen' => '2026-06-15',
            'metode_pembayaran' => 'TOP',
            'top_hari' => 30,
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseMissing('invoices', ['spb_id' => $spb->id]);
    }
}
