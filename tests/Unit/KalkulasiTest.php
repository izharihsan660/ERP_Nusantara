<?php

namespace Tests\Unit;

use App\Enums\MetodePembayaran;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class KalkulasiTest extends TestCase
{
    use CreatesErpFixtures;
    use RefreshDatabase;

    public function test_quotation_item_calculates_jumlah_and_profit(): void
    {
        $this->mockGeneratedPdfs();
        $data = $this->supportData('CALC');
        $quotation = $this->createQuotation($data, [
            'items' => [
                [
                    'katalog_id' => $data['katalog']->id,
                    'qty' => 3,
                    'harga_satuan' => 150000,
                ],
            ],
        ]);
        $item = $quotation->items->first();

        $this->assertEquals(450000, (float) $item->jumlah);
        $this->assertEquals(150000, (float) $item->profit);
    }

    public function test_invoice_calculates_due_date_from_top_days(): void
    {
        $invoice = new Invoice([
            'tgl_dokumen' => '2026-06-15',
            'metode_pembayaran' => MetodePembayaran::TOP,
            'top_hari' => 30,
        ]);

        $this->assertSame('2026-07-15', $invoice->hitungJatuhTempo()?->format('Y-m-d'));
    }

    public function test_invoice_detects_h7_due_date(): void
    {
        Carbon::setTestNow('2026-06-08');

        $invoice = new Invoice([
            'tgl_jatuh_tempo' => '2026-06-15',
        ]);

        $this->assertTrue($invoice->isJatuhTempoH7());

        Carbon::setTestNow();
    }
}
