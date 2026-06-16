<?php

namespace Tests\Feature;

use App\Enums\PDStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class PermissionFeatureTest extends TestCase
{
    use CreatesErpFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockGeneratedPdfs();
    }

    public function test_roles_are_forbidden_from_unauthorized_approval_or_creation_actions(): void
    {
        $data = $this->supportData('PERM');
        $this->actingAsRole('Superadmin');
        $quotation = $this->createPersistedQuotation($data, ['status' => QuotationStatus::PendingApproval]);
        $purchaseOrder = $this->createPersistedPurchaseOrder($data, ['status' => PurchaseOrderStatus::PendingApproval]);
        $pd = $this->createPersistedPermintaanDana(['status' => PDStatus::PendingApproval]);

        $this->actingAsRole('Sales');
        $this->post(route('quotations.approve', $quotation))->assertForbidden();

        $this->actingAsRole('Gudang');
        $this->post(route('quotations.store'), $this->quotationPayload($data))->assertForbidden();

        $this->actingAsRole('Finance');
        $this->post(route('permintaan-dana.approve', $pd))->assertForbidden();

        $this->actingAsRole('Procurement');
        $this->post(route('purchase-orders.approve', $purchaseOrder))->assertForbidden();
    }

    public function test_manager_can_approve_all_approval_documents(): void
    {
        $data = $this->supportData('PERM-MGR');
        $this->actingAsRole('Superadmin');
        $quotation = $this->createPersistedQuotation($data, ['status' => QuotationStatus::PendingApproval]);
        $purchaseOrder = $this->createPersistedPurchaseOrder($data, ['status' => PurchaseOrderStatus::PendingApproval]);
        $pd = $this->createPersistedPermintaanDana(['status' => PDStatus::PendingApproval]);

        $this->actingAsRole('Manager');
        $this->post(route('quotations.approve', $quotation))->assertRedirect();
        $this->post(route('purchase-orders.approve', $purchaseOrder))->assertRedirect();
        $this->post(route('permintaan-dana.approve', $pd))->assertRedirect();
    }

    public function test_superadmin_can_access_main_modules(): void
    {
        $this->actingAsRole('Superadmin');

        $this->get(route('quotations.index'))->assertOk();
        $this->get(route('purchase-orders.index'))->assertOk();
        $this->get(route('permintaan-dana.index'))->assertOk();
        $this->get(route('laporan.profit'))->assertOk();
    }

    private function createPersistedQuotation(array $data, array $overrides = []): Quotation
    {
        return Quotation::query()->create(array_replace([
            'no_quotation' => '001/QUOT/NAJ-MKS/VI/26',
            'tgl_quotation' => '2026-06-15',
            'customer_id' => $data['customer']->id,
            'template_id' => $data['quotationTemplate']->id,
            'status' => QuotationStatus::Draft,
            'created_by' => auth()->id(),
        ], $overrides));
    }
}
