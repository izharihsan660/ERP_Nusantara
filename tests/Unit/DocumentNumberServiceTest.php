<?php

namespace Tests\Unit;

use App\Services\DocumentNumberService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_document_number_formats(): void
    {
        $service = app(DocumentNumberService::class);
        $date = Carbon::parse('2026-03-15');

        $this->assertSame('001/QUOT/NAJ-MKS/III/26', $service->generateQuotationNumber($date));
        $this->assertSame('001/WHMKS/NAJ/III/26', $service->generateSpbNumber($date));
        $this->assertSame('001/NOTA-NAJ/MKS/NAJGROUP/III/2026', $service->generateInvoiceNumber($date));
        $this->assertSame('001/PO-NAJ/III/2026', $service->generatePurchaseOrderNumber($date));
        $this->assertSame('001/PD-NAJ/III/2026', $service->generatePermintaanDanaNumber($date));
    }

    public function test_sequence_increments_per_month(): void
    {
        $service = app(DocumentNumberService::class);
        $date = Carbon::parse('2026-06-15');

        $this->assertSame('001/QUOT/NAJ-MKS/VI/26', $service->generateQuotationNumber($date));
        $this->assertSame('002/QUOT/NAJ-MKS/VI/26', $service->generateQuotationNumber($date));
    }

    public function test_sequence_resets_for_new_year(): void
    {
        $service = app(DocumentNumberService::class);

        $this->assertSame('001/PD-NAJ/XII/2026', $service->generatePermintaanDanaNumber(Carbon::parse('2026-12-31')));
        $this->assertSame('001/PD-NAJ/I/2027', $service->generatePermintaanDanaNumber(Carbon::parse('2027-01-01')));
    }

    public function test_two_calls_for_a_new_month_create_one_counter_and_increment_safely(): void
    {
        $service = app(DocumentNumberService::class);
        $date = Carbon::parse('2030-07-01');

        $this->assertSame('001/PO-NAJ/VII/2030', $service->generatePurchaseOrderNumber($date));
        $this->assertSame('002/PO-NAJ/VII/2030', $service->generatePurchaseOrderNumber($date));
        $this->assertDatabaseCount('document_numbers', 1);
    }
}
