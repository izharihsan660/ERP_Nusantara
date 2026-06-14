<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe_vendor', ['RMA', 'VENDOR_LAIN']);
            $table->string('nama_vendor');
            $table->text('alamat')->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_email')->nullable();
            $table->string('rekening')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
