<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('katalog', function (Blueprint $table) {
            $table->id();
            $table->string('part_no')->unique();
            $table->string('nama_barang');
            $table->text('spesifikasi')->nullable();
            $table->string('satuan')->nullable();
            $table->decimal('hpp', 15, 2)->default(0);
            $table->decimal('harga_jual_default', 15, 2)->default(0);
            $table->string('kategori')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('katalog');
    }
};
