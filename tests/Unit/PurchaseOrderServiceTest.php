<?php

namespace Tests\Unit;

use App\Enums\PurchaseOrderStatus;
use App\Models\User;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class PurchaseOrderServiceTest extends TestCase
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

    public function test_create_stores_purchase_order_as_draft(): void
    {
        $purchaseOrder = app(PurchaseOrderService::class)->create($this->purchaseOrderPayload($this->supportData('PO')), $this->user);

        $this->assertSame(PurchaseOrderStatus::Draft, $purchaseOrder->status);
        $this->assertCount(1, $purchaseOrder->items);
    }

    public function test_submit_moves_to_pending_and_requires_draft(): void
    {
        $purchaseOrder = $this->createPurchaseOrder($this->supportData('PO-SUBMIT'));

        $submitted = app(PurchaseOrderService::class)->submit($purchaseOrder, $this->user);
        $this->assertSame(PurchaseOrderStatus::PendingApproval, $submitted->status);

        $this->expectException(ValidationException::class);
        app(PurchaseOrderService::class)->submit($submitted, $this->user);
    }

    public function test_submit_fails_without_items(): void
    {
        $purchaseOrder = $this->createPersistedPurchaseOrder($this->supportData('PO-NO-ITEM'));

        $this->expectException(ValidationException::class);
        app(PurchaseOrderService::class)->submit($purchaseOrder, $this->user);
    }

    public function test_approve_sets_approved_status_qr_token_and_pdf(): void
    {
        $purchaseOrder = $this->createPurchaseOrder($this->supportData('PO-APPROVE'));
        app(PurchaseOrderService::class)->submit($purchaseOrder, $this->user);

        $approved = app(PurchaseOrderService::class)->approve($purchaseOrder->refresh(), $this->user);

        $this->assertSame(PurchaseOrderStatus::Approved, $approved->status);
        $this->assertNotEmpty($approved->qr_token);
    }

    public function test_reject_returns_purchase_order_to_draft(): void
    {
        $purchaseOrder = $this->createPurchaseOrder($this->supportData('PO-REJECT'));
        app(PurchaseOrderService::class)->submit($purchaseOrder, $this->user);

        $rejected = app(PurchaseOrderService::class)->reject($purchaseOrder->refresh(), 'Perlu revisi.', $this->user);

        $this->assertSame(PurchaseOrderStatus::Draft, $rejected->status);
        $this->assertSame('Perlu revisi.', $rejected->catatan_rejection);
        $this->assertSame('Catatan PO', $rejected->catatan);
    }

    public function test_void_sets_status_to_void(): void
    {
        $purchaseOrder = $this->createPurchaseOrder($this->supportData('PO-VOID'));

        $voided = app(PurchaseOrderService::class)->void($purchaseOrder, 'Batal.', $this->user);

        $this->assertSame(PurchaseOrderStatus::Void, $voided->status);
    }

    public function test_void_is_rejected_when_purchase_order_has_active_spb(): void
    {
        $data = $this->supportData('PO-VOID-SPB');
        $purchaseOrder = $this->createApprovedPurchaseOrder($data);
        $this->createSpbFromPurchaseOrder($purchaseOrder, $data);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('PO tidak bisa di-void karena sudah memiliki SPB/Invoice aktif.');

        app(PurchaseOrderService::class)->void($purchaseOrder->refresh(), 'Batal.', $this->user);
    }
}
