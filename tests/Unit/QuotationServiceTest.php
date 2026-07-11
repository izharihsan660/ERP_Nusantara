<?php

namespace Tests\Unit;

use App\Enums\QuotationStatus;
use App\Models\User;
use App\Services\QuotationPDFService;
use App\Services\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class QuotationServiceTest extends TestCase
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

    public function test_create_stores_draft_with_number_and_calculated_items(): void
    {
        $data = $this->supportData('QUOT');
        $quotation = app(QuotationService::class)->create($this->quotationPayload($data), $this->user);

        $this->assertSame(QuotationStatus::Draft, $quotation->status);
        $this->assertMatchesRegularExpression('/^\d{3}\/QUOT\/NAJ-MKS\/VI\/26$/', $quotation->no_quotation);
        $this->assertCount(1, $quotation->items);
        $this->assertEquals(300000, (float) $quotation->items->first()->jumlah);
        $this->assertEquals(100000, (float) $quotation->items->first()->profit);
    }

    public function test_submit_requires_draft_and_moves_to_pending(): void
    {
        $quotation = $this->createQuotation($this->supportData('SUBMIT'));

        $submitted = app(QuotationService::class)->submit($quotation, $this->user);
        $this->assertSame(QuotationStatus::PendingApproval, $submitted->status);

        $this->expectException(ValidationException::class);
        app(QuotationService::class)->submit($submitted, $this->user);
    }

    public function test_approve_generates_qr_token_and_pdf(): void
    {
        $quotation = $this->createQuotation($this->supportData('APPROVE'));
        app(QuotationService::class)->submit($quotation, $this->user);

        $approved = app(QuotationService::class)->approve($quotation->refresh(), $this->user);

        $this->assertSame(QuotationStatus::Approved, $approved->status);
        $this->assertNotEmpty($approved->qr_token);
    }

    public function test_approve_remains_committed_when_pdf_generation_fails(): void
    {
        $quotation = $this->createQuotation($this->supportData('APPROVE-PDF-FAIL'));
        app(QuotationService::class)->submit($quotation, $this->user);

        $this->mock(QuotationPDFService::class)
            ->shouldReceive('generate')
            ->once()
            ->andThrow(new \RuntimeException('PDF service unavailable'));

        $approved = app(QuotationService::class)->approve($quotation->refresh(), $this->user);

        $this->assertSame(QuotationStatus::Approved, $approved->status);
        $this->assertSame($this->user->id, $approved->approved_by);
        $this->assertNotEmpty($approved->qr_token);
        $this->assertNull($approved->generated_pdf_path);
    }

    public function test_reject_stores_note(): void
    {
        $quotation = $this->createQuotation($this->supportData('REJECT'));
        app(QuotationService::class)->submit($quotation, $this->user);

        $rejected = app(QuotationService::class)->reject($quotation->refresh(), 'Harga belum sesuai.', $this->user);

        $this->assertSame(QuotationStatus::Rejected, $rejected->status);
        $this->assertSame('Harga belum sesuai.', $rejected->catatan_rejection);
    }

    public function test_void_moves_status_and_fails_when_already_void(): void
    {
        $quotation = $this->createQuotation($this->supportData('VOID'));

        $voided = app(QuotationService::class)->void($quotation, 'Salah input.', $this->user);
        $this->assertSame(QuotationStatus::Void, $voided->status);

        $this->expectException(ValidationException::class);
        app(QuotationService::class)->void($voided, 'Void lagi.', $this->user);
    }

    public function test_duplicate_creates_new_revision(): void
    {
        $quotation = $this->createQuotation($this->supportData('DUP'));

        $duplicate = app(QuotationService::class)->duplicate($quotation, $this->user);

        $this->assertNotSame($quotation->id, $duplicate->id);
        $this->assertSame($quotation->revisi + 1, $duplicate->revisi);
        $this->assertSame(QuotationStatus::Draft, $duplicate->status);
    }
}
