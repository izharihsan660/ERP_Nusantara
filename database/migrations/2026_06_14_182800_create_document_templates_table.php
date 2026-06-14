<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama_template');
            $table->string('kode_template')->unique();
            $table->enum('tipe_dokumen', ['QUOTATION', 'SPB', 'INVOICE', 'NOTA', 'PO_NAJ']);
            $table->string('blade_file');
            $table->boolean('is_default')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
