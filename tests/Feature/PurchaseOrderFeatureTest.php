<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class PurchaseOrderFeatureTest extends TestCase
{
    use CreatesErpFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockGeneratedPdfs();
    }

    public function test_index_store_submit_approve_void_and_download_flow(): void
    {
        $data = $this->supportData('PO-FEATURE');
        $this->actingAsRole('Superadmin');

        $this->get(route('purchase-orders.index'))->assertOk();
        $this->post(route('purchase-orders.store'), $this->purchaseOrderPayload($data))->assertRedirect();

        $purchaseOrder = PurchaseOrder::query()->latest('id')->firstOrFail();
        $this->assertSame(PurchaseOrderStatus::Draft, $purchaseOrder->status);

        $this->post(route('purchase-orders.submit', $purchaseOrder))->assertRedirect();
        $this->assertSame(PurchaseOrderStatus::PendingApproval, $purchaseOrder->refresh()->status);

        $this->actingAsRole('Procurement');
        $this->post(route('purchase-orders.approve', $purchaseOrder))->assertForbidden();

        $this->actingAsRole('Manager');
        $this->post(route('purchase-orders.approve', $purchaseOrder))->assertRedirect();
        $this->assertSame(PurchaseOrderStatus::Approved, $purchaseOrder->refresh()->status);

        $this->putPdf('purchase-orders/test.pdf');
        $this->actingAsRole('Superadmin');
        $this->get(route('purchase-orders.download', $purchaseOrder))->assertOk()->assertHeader('content-type', 'application/pdf');

        $this->post(route('purchase-orders.void', $purchaseOrder), ['alasan_void' => 'Batal vendor'])->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'status' => PurchaseOrderStatus::Void->value,
            'alasan_void' => 'Batal vendor',
        ]);
    }
}
