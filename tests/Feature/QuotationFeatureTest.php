<?php

namespace Tests\Feature;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class QuotationFeatureTest extends TestCase
{
    use CreatesErpFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockGeneratedPdfs();
    }

    public function test_guest_is_redirected_and_user_without_permission_is_forbidden(): void
    {
        $this->get(route('quotations.index'))->assertRedirect(route('login'));

        $this->actingAsUserWithPermissions([]);
        $this->get(route('quotations.index'))->assertForbidden();
    }

    public function test_index_and_store_work_for_authorized_user(): void
    {
        $data = $this->supportData('QUOT-FEATURE');
        $this->actingAsRole('Superadmin');

        $this->get(route('quotations.index'))->assertOk();
        $this->post(route('quotations.store'), $this->quotationPayload($data))->assertRedirect();

        $this->assertDatabaseHas('quotations', [
            'customer_id' => $data['customer']->id,
            'status' => QuotationStatus::Draft->value,
        ]);
    }

    public function test_submit_approve_void_and_download_flow(): void
    {
        $data = $this->supportData('QUOT-ACTIONS');
        $this->actingAsRole('Superadmin');
        $quotation = $this->createQuotation($data);

        $this->post(route('quotations.submit', $quotation))->assertRedirect();
        $this->assertSame(QuotationStatus::PendingApproval, $quotation->refresh()->status);

        $this->actingAsRole('Sales');
        $this->post(route('quotations.approve', $quotation))->assertForbidden();

        $this->actingAsRole('Manager');
        $this->post(route('quotations.approve', $quotation))->assertRedirect();
        $this->assertSame(QuotationStatus::Approved, $quotation->refresh()->status);

        $this->putPdf('quotations/test.pdf');
        $this->actingAsRole('Superadmin');
        $this->get(route('quotations.download', $quotation))->assertOk()->assertHeader('content-type', 'application/pdf');

        $this->post(route('quotations.void', $quotation), ['alasan_void' => 'Batal customer'])->assertRedirect();
        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => QuotationStatus::Void->value,
            'alasan_void' => 'Batal customer',
        ]);
    }

    public function test_approve_is_manager_only(): void
    {
        $quotation = Quotation::query()->create([
            'no_quotation' => '001/QUOT/NAJ-MKS/VI/26',
            'tgl_quotation' => '2026-06-15',
            'customer_id' => $this->supportData('QUOT-MGR')['customer']->id,
            'template_id' => $this->supportData('QUOT-MGR-2')['quotationTemplate']->id,
            'status' => QuotationStatus::PendingApproval,
            'created_by' => $this->actingAsRole('Superadmin')->id,
        ]);

        $this->actingAsRole('Sales');
        $this->post(route('quotations.approve', $quotation))->assertForbidden();

        $this->actingAsRole('Manager');
        $this->post(route('quotations.approve', $quotation))->assertRedirect();
    }
}
