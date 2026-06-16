<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Models\Customer;
use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $defaultQuotationTemplate = DocumentTemplate::updateOrCreate(
            ['kode_template' => 'QUOTATION-DEFAULT'],
            [
                'nama_template' => 'Quotation Umum',
                'tipe_dokumen' => DocumentType::Quotation,
                'blade_file' => 'pdf.quotation.default',
                'is_default' => true,
                'keterangan' => 'Template standar quotation.',
            ],
        );

        DocumentTemplate::updateOrCreate(
            ['kode_template' => 'QUOTATION-MIL'],
            [
                'nama_template' => 'Quotation MIL',
                'tipe_dokumen' => DocumentType::Quotation,
                'blade_file' => 'pdf.quotation.mil',
                'is_default' => false,
                'keterangan' => 'Template quotation khusus MIL.',
            ],
        );

        $defaultSpbTemplate = DocumentTemplate::updateOrCreate(
            ['kode_template' => 'SPB-DEFAULT'],
            [
                'nama_template' => 'SPB Umum',
                'tipe_dokumen' => DocumentType::Spb,
                'blade_file' => 'pdf.spb.default',
                'is_default' => true,
                'keterangan' => 'Template standar SPB.',
            ],
        );

        DocumentTemplate::updateOrCreate(
            ['kode_template' => 'SPB-MIL'],
            [
                'nama_template' => 'SPB MIL',
                'tipe_dokumen' => DocumentType::Spb,
                'blade_file' => 'pdf.spb.mil',
                'is_default' => false,
                'keterangan' => 'Template SPB khusus MIL.',
            ],
        );

        Customer::query()
            ->whereNull('template_quotation_id')
            ->update(['template_quotation_id' => $defaultQuotationTemplate->id]);

        Customer::query()
            ->whereNull('template_spb_id')
            ->update(['template_spb_id' => $defaultSpbTemplate->id]);

        $superadmin = User::updateOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'superadmin@naj.local')],
            [
                'name' => env('SUPERADMIN_NAME', 'Superadmin NAJ'),
                'email_verified_at' => now(),
                'password' => Hash::make(env('SUPERADMIN_PASSWORD', 'password')),
                'is_active' => true,
            ],
        );

        $superadmin->assignRole('Superadmin');
    }
}
