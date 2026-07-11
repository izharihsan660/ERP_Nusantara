<?php

namespace Tests\Feature;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class ApprovalLinkFeatureTest extends TestCase
{
    use CreatesErpFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockGeneratedPdfs();
        $this->seedPermissions();
    }

    public function test_get_only_shows_confirmation_and_post_approves_as_signed_approver(): void
    {
        $quotation = $this->pendingQuotation('APPROVAL-LINK');
        $approver = $this->manager();
        $url = URL::temporarySignedRoute('approval.approve', now()->addDay(), [
            'type' => 'quotation',
            'id' => $quotation->id,
            'approver' => $approver->id,
        ]);

        $this->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ApprovalConfirm')
                ->where('confirmation', true)
                ->where('action', 'approve'));

        $this->assertSame(QuotationStatus::PendingApproval, $quotation->refresh()->status);

        $this->post($url)->assertOk();

        $quotation->refresh();
        $this->assertSame(QuotationStatus::Approved, $quotation->status);
        $this->assertSame($approver->id, $quotation->approved_by);
    }

    public function test_reject_post_uses_signed_approver_and_correct_service_argument_order(): void
    {
        $quotation = $this->pendingQuotation('REJECT-LINK');
        $approver = $this->manager();
        $url = URL::temporarySignedRoute('approval.reject', now()->addDay(), [
            'type' => 'quotation',
            'id' => $quotation->id,
            'approver' => $approver->id,
        ]);

        $this->get($url)->assertOk();
        $this->assertSame(QuotationStatus::PendingApproval, $quotation->refresh()->status);

        $this->post($url)->assertOk();

        $quotation->refresh();
        $this->assertSame(QuotationStatus::Rejected, $quotation->status);
        $this->assertSame('Rejected via email approval link', $quotation->catatan_rejection);
    }

    private function pendingQuotation(string $suffix): Quotation
    {
        $data = $this->supportData($suffix);
        $creator = User::factory()->create(['is_active' => true]);

        return Quotation::query()->create([
            'no_quotation' => $suffix,
            'tgl_quotation' => '2026-07-01',
            'customer_id' => $data['customer']->id,
            'template_id' => $data['quotationTemplate']->id,
            'status' => QuotationStatus::PendingApproval,
            'created_by' => $creator->id,
        ]);
    }

    private function manager(): User
    {
        $manager = User::factory()->create(['is_active' => true]);
        $manager->assignRole(Role::findByName('Manager'));

        return $manager;
    }
}
