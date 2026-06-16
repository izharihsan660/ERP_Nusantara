<?php

namespace Tests\Feature;

use App\Enums\PDStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class VerifyFeatureTest extends TestCase
{
    use CreatesErpFixtures;
    use RefreshDatabase;

    public function test_invalid_token_shows_not_valid_message(): void
    {
        $this->get(route('verify.quotation', 'invalid-token'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('valid', false));
    }

    public function test_valid_tokens_show_document_info(): void
    {
        $data = $this->supportData('VERIFY');
        $user = User::factory()->create(['is_active' => true]);

        $quotation = Quotation::query()->create([
            'no_quotation' => '001/QUOT/NAJ-MKS/VI/26',
            'tgl_quotation' => '2026-06-15',
            'customer_id' => $data['customer']->id,
            'template_id' => $data['quotationTemplate']->id,
            'status' => QuotationStatus::Approved,
            'qr_token' => 'quotation-token',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'created_by' => $user->id,
        ]);

        $purchaseOrder = $this->createPersistedPurchaseOrder($data, [
            'status' => PurchaseOrderStatus::Approved,
            'qr_token' => 'po-token',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $pd = $this->createPersistedPermintaanDana([
            'status' => PDStatus::Approved,
            'qr_token' => 'pd-token',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $this->get(route('verify.quotation', $quotation->qr_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('valid', true)
                ->where('document.nomor', $quotation->no_quotation));

        $this->get(route('verify.quotation', $purchaseOrder->qr_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('valid', true)
                ->where('document.nomor', $purchaseOrder->no_purchase_order));

        $this->get(route('verify.quotation', $pd->qr_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('valid', true)
                ->where('document.nomor', $pd->no_pd));
    }
}
