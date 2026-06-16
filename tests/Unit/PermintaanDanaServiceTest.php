<?php

namespace Tests\Unit;

use App\Enums\PDStatus;
use App\Models\User;
use App\Services\PermintaanDanaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class PermintaanDanaServiceTest extends TestCase
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

    public function test_create_stores_draft(): void
    {
        $pd = app(PermintaanDanaService::class)->create($this->permintaanDanaPayload(), $this->user);

        $this->assertSame(PDStatus::Draft, $pd->status);
    }

    public function test_submit_and_approve_flow_generates_qr_code(): void
    {
        $pd = $this->createPermintaanDana();

        $submitted = app(PermintaanDanaService::class)->submit($pd, $this->user);
        $this->assertSame(PDStatus::PendingApproval, $submitted->status);

        $approved = app(PermintaanDanaService::class)->approve($submitted, $this->user);
        $this->assertSame(PDStatus::Approved, $approved->status);
        $this->assertNotEmpty($approved->qr_token);
    }

    public function test_reject_stores_note(): void
    {
        $pd = $this->createPermintaanDana();
        app(PermintaanDanaService::class)->submit($pd, $this->user);

        $rejected = app(PermintaanDanaService::class)->reject($pd->refresh(), 'Lampiran kurang.', $this->user);

        $this->assertSame(PDStatus::Rejected, $rejected->status);
        $this->assertSame('Lampiran kurang.', $rejected->catatan_rejection);
    }

    public function test_upload_bukti_sets_paid_status(): void
    {
        $pd = $this->createPermintaanDana();
        app(PermintaanDanaService::class)->submit($pd, $this->user);
        app(PermintaanDanaService::class)->approve($pd->refresh(), $this->user);

        $paid = app(PermintaanDanaService::class)->uploadBukti($pd->refresh(), [
            'tgl_realisasi' => '2026-06-16',
            'jumlah_realisasi' => 2500000,
            'file_bukti' => UploadedFile::fake()->image('bukti.png'),
        ], $this->user);

        $this->assertSame(PDStatus::Paid, $paid->status);
    }

    public function test_upload_bukti_fails_when_status_is_not_approved(): void
    {
        $pd = $this->createPermintaanDana();

        $this->expectException(ValidationException::class);
        app(PermintaanDanaService::class)->uploadBukti($pd, [
            'tgl_realisasi' => '2026-06-16',
            'jumlah_realisasi' => 2500000,
            'file_bukti' => UploadedFile::fake()->image('bukti.png'),
        ], $this->user);
    }

    public function test_void_fails_when_status_is_paid(): void
    {
        $pd = $this->createPersistedPermintaanDana(['status' => PDStatus::Paid]);

        $this->expectException(ValidationException::class);
        app(PermintaanDanaService::class)->void($pd, 'Sudah paid.', $this->user);
    }
}
