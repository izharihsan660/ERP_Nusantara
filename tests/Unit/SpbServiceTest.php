<?php

namespace Tests\Unit;

use App\Enums\ReferensiTipe;
use App\Enums\StatusSupply;
use App\Models\User;
use App\Services\SpbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class SpbServiceTest extends TestCase
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

    public function test_create_from_wip_marks_wip_supplied(): void
    {
        $data = $this->supportData('SPB-WIP');
        $wip = $this->createWipOrder($this->createSalesOrder($this->createApprovedQuotation($data)));

        $spb = $this->createSpbFromWip($wip, $data);

        $this->assertSame($wip->id, $spb->spb_able_id);
        $this->assertSame(StatusSupply::Tersupply, $wip->refresh()->status_supply);
    }

    public function test_create_from_purchase_order_uses_po_customer(): void
    {
        $data = $this->supportData('SPB-PO');
        $purchaseOrder = $this->createApprovedPurchaseOrder($data, ['no_po_customer' => 'PO-CUST-SPB']);

        $spb = $this->createSpbFromPurchaseOrder($purchaseOrder, $data);

        $this->assertSame($data['customer']->id, $spb->customer_id);
        $this->assertSame(ReferensiTipe::PO, $spb->referensi_tipe);
        $this->assertSame('PO-CUST-SPB', $spb->no_referensi);
    }

    public function test_create_uses_pr_reference_when_po_customer_is_empty(): void
    {
        $data = $this->supportData('SPB-PR');
        $purchaseOrder = $this->createApprovedPurchaseOrder($data, ['no_pr_customer' => 'PR-CUST-SPB', 'no_po_customer' => null]);

        $spb = $this->createSpbFromPurchaseOrder($purchaseOrder, $data);

        $this->assertSame(ReferensiTipe::PR, $spb->referensi_tipe);
        $this->assertSame('PR-CUST-SPB', $spb->no_referensi);
    }

    public function test_update_referensi_changes_pr_to_po_when_po_is_available(): void
    {
        $data = $this->supportData('SPB-UPDATE-REF');
        $purchaseOrder = $this->createApprovedPurchaseOrder($data, ['no_pr_customer' => 'PR-OLD', 'no_po_customer' => null]);
        $spb = $this->createSpbFromPurchaseOrder($purchaseOrder, $data);
        $purchaseOrder->update(['no_po_customer' => 'PO-NEW']);

        $updated = app(SpbService::class)->updateReferensi($spb->refresh(), $this->user);

        $this->assertSame(ReferensiTipe::PO, $updated->referensi_tipe);
        $this->assertSame('PO-NEW', $updated->no_referensi);
    }

    public function test_void_resets_wip_when_spb_is_only_active_shipment(): void
    {
        $data = $this->supportData('SPB-VOID');
        $wip = $this->createWipOrder($this->createSalesOrder($this->createApprovedQuotation($data)));
        $spb = $this->createSpbFromWip($wip, $data);

        app(SpbService::class)->void($spb, 'Salah kirim.', $this->user);

        $this->assertSame(StatusSupply::BelumTersupply, $wip->refresh()->status_supply);
    }

    public function test_void_fails_when_active_invoice_exists(): void
    {
        $data = $this->supportData('SPB-INVOICE');
        $wip = $this->createWipOrder($this->createSalesOrder($this->createApprovedQuotation($data)));
        $spb = $this->createSpbFromWip($wip, $data);
        $this->createPersistedInvoice($data, ['spb' => $spb]);

        $this->expectException(ValidationException::class);
        app(SpbService::class)->void($spb->refresh(), 'Sudah ada invoice.', $this->user);
    }
}
