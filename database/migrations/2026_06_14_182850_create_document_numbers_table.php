<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('tipe_dokumen', 50);
            $table->year('tahun');
            $table->tinyInteger('bulan')->unsigned();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['tipe_dokumen', 'tahun', 'bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_numbers');
    }
};
