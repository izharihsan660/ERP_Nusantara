<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            if (! Schema::hasColumn('quotations', 'generated_pdf_path')) {
                $table->string('generated_pdf_path')->nullable()->after('qr_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            if (Schema::hasColumn('quotations', 'generated_pdf_path')) {
                $table->dropColumn('generated_pdf_path');
            }
        });
    }
};
