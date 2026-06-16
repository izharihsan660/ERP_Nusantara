<?php

namespace Tests\Unit;

use App\Enums\SalesOrderStatus;
use App\Enums\WIPStatus;
use App\Models\User;
use App\Services\SalesOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class SalesOrderServiceTest extends TestCase
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

    public function test_create_stores_sales_order_for_approved_quotation(): void
    {
        $quotation = $this->createApprovedQuotation($this->supportData('SO'));

        $salesOrder = $this->createSalesOrder($quotation);

        $this->assertSame($quotation->id, $salesOrder->quotation_id);
        $this->assertSame(SalesOrderStatus::Open, $salesOrder->status);
    }

    public function test_create_fails_when_quotation_is_not_approved(): void
    {
        $quotation = $this->createQuotation($this->supportData('SO-NOT-APPROVED'));

        $this->expectException(ValidationException::class);
        $this->createSalesOrder($quotation);
    }

    public function test_create_fails_when_quotation_already_has_sales_order(): void
    {
        $quotation = $this->createApprovedQuotation($this->supportData('SO-DUP'));
        $this->createSalesOrder($quotation);

        $this->expectException(ValidationException::class);
        $this->createSalesOrder($quotation->refresh());
    }

    public function test_void_fails_when_active_wip_exists(): void
    {
        $quotation = $this->createApprovedQuotation($this->supportData('SO-WIP'));
        $salesOrder = $this->createSalesOrder($quotation);
        $this->createPersistedWip($salesOrder, ['status' => WIPStatus::Active]);

        $this->expectException(ValidationException::class);
        app(SalesOrderService::class)->void($salesOrder->refresh(), 'Ada WIP aktif.', $this->user);
    }
}
