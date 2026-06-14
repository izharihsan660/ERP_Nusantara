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

        Customer::query()
            ->whereNull('template_quotation_id')
            ->update(['template_quotation_id' => $defaultQuotationTemplate->id]);

        $superadmin = User::updateOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'superadmin@naj.local')],
            [
                'name' => env('SUPERADMIN_NAME', 'Superadmin NAJ'),
                'password' => Hash::make(env('SUPERADMIN_PASSWORD', 'password')),
                'is_active' => true,
            ],
        );

        $superadmin->assignRole('Superadmin');
    }
}
