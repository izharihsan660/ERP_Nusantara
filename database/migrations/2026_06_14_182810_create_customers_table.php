<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_customer')->unique();
            $table->string('nama_customer');
            $table->text('alamat')->nullable();
            $table->string('kota')->nullable();
            $table->string('npwp')->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_email')->nullable();
            $table->string('pic_phone')->nullable();
            $table->foreignId('template_quotation_id')->nullable()->constrained('document_templates')->nullOnDelete();
            $table->foreignId('template_spb_id')->nullable()->constrained('document_templates')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
