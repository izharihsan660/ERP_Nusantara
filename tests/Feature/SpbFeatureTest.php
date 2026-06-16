<?php

namespace Tests\Feature;

use App\Enums\SpbStatus;
use App\Models\Spb;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class SpbFeatureTest extends TestCase
{
    use CreatesErpFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockGeneratedPdfs();
    }

    public function test_store_from_wip_and_purchase_order_then_download_and_void(): void
    {
        $this->actingAsRole('Superadmin');

        $wipData = $this->supportData('SPB-FEATURE-WIP');
        $wip = $this->createWipOrder($this->createSalesOrder($this->createApprovedQuotation($wipData)));
        $this->post(route('wip-orders.spb.store', $wip), $this->spbPayload($wipData))->assertRedirect();
        $wipSpb = Spb::query()->where('spb_able_id', $wip->id)->latest('id')->firstOrFail();
        $this->assertSame(SpbStatus::Shipped, $wipSpb->status);

        $poData = $this->supportData('SPB-FEATURE-PO');
        $purchaseOrder = $this->createApprovedPurchaseOrder($poData);
        $this->post(route('purchase-orders.spb.store', $purchaseOrder), $this->spbPayload($poData))->assertRedirect();
        $poSpb = Spb::query()->where('spb_able_id', $purchaseOrder->id)->latest('id')->firstOrFail();
        $this->assertSame($poData['customer']->id, $poSpb->customer_id);

        $this->putPdf('spb/test.pdf');
        $this->get(route('spb.download', $wipSpb))->assertOk()->assertHeader('content-type', 'application/pdf');

        $this->post(route('spb.void', $wipSpb), ['alasan_void' => 'Salah pengiriman'])->assertRedirect();
        $this->assertDatabaseHas('spb', [
            'id' => $wipSpb->id,
            'status' => SpbStatus::Void->value,
        ]);
    }
}
