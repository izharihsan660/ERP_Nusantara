<?php

namespace Tests\Feature;

use App\Enums\PdDocumentKategori;
use App\Enums\PDStatus;
use App\Models\PermintaanDana;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class PermintaanDanaFeatureTest extends TestCase
{
    use CreatesErpFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockGeneratedPdfs();
    }

    public function test_pd_store_submit_approve_upload_bukti_and_void_validation(): void
    {
        $this->actingAsRole('Superadmin');

        $this->get(route('permintaan-dana.index'))->assertOk();
        $this->post(route('permintaan-dana.store'), $this->permintaanDanaPayload())->assertRedirect();

        $pd = PermintaanDana::query()->latest('id')->firstOrFail();
        $this->assertSame(PDStatus::Draft, $pd->status);

        $this->post(route('permintaan-dana.submit', $pd))->assertRedirect();
        $this->assertSame(PDStatus::PendingApproval, $pd->refresh()->status);

        $this->actingAsRole('Finance');
        $this->post(route('permintaan-dana.approve', $pd))->assertForbidden();

        $this->actingAsRole('Manager');
        $this->post(route('permintaan-dana.approve', $pd))->assertRedirect();
        $this->assertSame(PDStatus::Approved, $pd->refresh()->status);

        $this->actingAsRole('Superadmin');
        $this->post(route('permintaan-dana.upload-bukti', $pd), [
            'tgl_realisasi' => '2026-06-16',
            'jumlah_realisasi' => 2500000,
            'documents' => [
                [
                    'kategori' => PdDocumentKategori::BuktiPembelian->value,
                    'file' => UploadedFile::fake()->image('bukti.png'),
                ],
            ],
        ])->assertRedirect();
        $this->assertSame(PDStatus::Paid, $pd->refresh()->status);
        $this->assertSame(1, $pd->documents()->count());

        $this->post(route('permintaan-dana.void', $pd), ['alasan_void' => 'Sudah paid'])
            ->assertSessionHasErrors('status');
    }
}
