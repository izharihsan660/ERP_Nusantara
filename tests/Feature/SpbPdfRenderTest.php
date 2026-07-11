<?php

namespace Tests\Feature;

use App\Services\SpbPDFService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class SpbPdfRenderTest extends TestCase
{
    use CreatesErpFixtures;
    use RefreshDatabase;

    public function test_spb_with_sixty_items_renders_as_multipage_pdf(): void
    {
        Storage::fake('local');

        $data = $this->supportData('SPB-PDF-RENDER');
        $spb = $this->createPersistedSpb($data);

        $spb->items()->createMany(
            collect(range(1, 60))->map(fn (int $number): array => [
                'part_no' => "PART-{$number}",
                'deskripsi' => "Deskripsi item SPB multi halaman {$number}",
                'qty' => $number,
                'berat' => 1.25,
                'volume' => 0.5,
                'dimensi' => '10x20x30',
                'sku' => 'PCS',
            ])->all(),
        );

        $path = app(SpbPDFService::class)->generate($spb->refresh());

        Storage::disk('local')->assertExists($path);
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($path));
    }
}
