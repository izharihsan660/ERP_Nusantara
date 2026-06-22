<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_templates', function (Blueprint $table): void {
            if (! Schema::hasColumn('document_templates', 'docx_path')) {
                $table->string('docx_path')->nullable()->after('blade_file');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_templates', function (Blueprint $table): void {
            if (Schema::hasColumn('document_templates', 'docx_path')) {
                $table->dropColumn('docx_path');
            }
        });
    }
};
