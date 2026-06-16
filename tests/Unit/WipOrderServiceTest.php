<?php

namespace Tests\Unit;

use App\Enums\StatusSupply;
use App\Enums\TipeOrder;
use App\Models\User;
use App\Services\WipOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class WipOrderServiceTest extends TestCase
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

    public function test_create_stores_wip_with_belum_tersupply_status(): void
    {
        $salesOrder = $this->createSalesOrder($this->createApprovedQuotation($this->supportData('WIP')));

        $wip = $this->createWipOrder($salesOrder);

        $this->assertSame(StatusSupply::BelumTersupply, $wip->status_supply);
    }

    public function test_vor_requires_expedition_name(): void
    {
        $salesOrder = $this->createSalesOrder($this->createApprovedQuotation($this->supportData('WIP-VOR')));

        $this->expectException(ValidationException::class);
        $this->createWipOrder($salesOrder, ['tipe_order' => TipeOrder::VOR->value, 'nama_ekspedisi' => '']);
    }

    public function test_stk_allows_empty_expedition_name(): void
    {
        $salesOrder = $this->createSalesOrder($this->createApprovedQuotation($this->supportData('WIP-STK')));

        $wip = $this->createWipOrder($salesOrder, ['tipe_order' => TipeOrder::STK->value, 'nama_ekspedisi' => '']);

        $this->assertNull($wip->nama_ekspedisi);
    }

    public function test_void_fails_when_wip_is_already_supplied(): void
    {
        $salesOrder = $this->createSalesOrder($this->createApprovedQuotation($this->supportData('WIP-VOID')));
        $wip = $this->createWipOrder($salesOrder);
        $wip->update(['status_supply' => StatusSupply::Tersupply]);

        $this->expectException(ValidationException::class);
        app(WipOrderService::class)->void($wip->refresh(), 'Sudah tersupply.', $this->user);
    }
}
