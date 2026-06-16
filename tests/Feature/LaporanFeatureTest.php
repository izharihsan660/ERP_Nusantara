<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesErpFixtures;
use Tests\TestCase;

class LaporanFeatureTest extends TestCase
{
    use CreatesErpFixtures;
    use RefreshDatabase;

    public function test_report_pages_and_export_are_accessible(): void
    {
        $this->actingAsRole('Superadmin');

        $this->get(route('laporan.rekapan-po'))->assertOk();
        $this->get(route('laporan.rekapan-wip'))->assertOk();
        $this->get(route('laporan.rekapan-spb'))->assertOk();
        $this->get(route('laporan.rekapan-invoice'))->assertOk();
        $this->get(route('laporan.rekapan-pd'))->assertOk();
        $this->get(route('laporan.profit'))->assertOk();
        $this->get(route('laporan.outstanding'))->assertOk();

        $this->get(route('laporan.export', 'rekapan-po'))
            ->assertOk()
            ->assertHeader('content-disposition');
    }
}
