<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('katalog_id')->constrained('katalog')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('part_no', 50);
            $table->string('deskripsi', 200);
            $table->unsignedInteger('qty');
            $table->string('satuan', 20)->nullable();
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('hpp_satuan', 15, 2);
            $table->decimal('jumlah', 15, 2);
            $table->decimal('profit', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
