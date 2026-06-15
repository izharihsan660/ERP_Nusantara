<?php

use App\Enums\InvoiceStatus;
use App\Enums\MetodePembayaran;
use App\Enums\StatusPembayaran;
use App\Enums\TipeDokumen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('no_dokumen', 50)->unique();
            $table->enum('tipe_dokumen', array_map(fn (TipeDokumen $tipe): string => $tipe->value, TipeDokumen::cases()));
            $table->date('tgl_dokumen');
            $table->foreignId('spb_id')->unique()->constrained('spb')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('no_faktur_pajak', 50)->nullable();
            $table->decimal('total_nilai', 15, 2);
            $table->decimal('total_hpp', 15, 2)->default(0);
            $table->decimal('total_profit', 15, 2)->default(0);
            $table->enum('metode_pembayaran', array_map(fn (MetodePembayaran $metode): string => $metode->value, MetodePembayaran::cases()));
            $table->unsignedInteger('top_hari')->nullable();
            $table->date('tgl_jatuh_tempo')->nullable();
            $table->enum('status_pembayaran', array_map(fn (StatusPembayaran $status): string => $status->value, StatusPembayaran::cases()))->default(StatusPembayaran::Belum->value);
            $table->date('tgl_bayar')->nullable();
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->string('file_ttd_gabungan', 255)->nullable();
            $table->enum('status', array_map(fn (InvoiceStatus $status): string => $status->value, InvoiceStatus::cases()))->default(InvoiceStatus::Active->value);
            $table->text('alasan_void')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->index(['customer_id', 'tgl_dokumen']);
            $table->index(['metode_pembayaran', 'tgl_jatuh_tempo']);
            $table->index(['status_pembayaran', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
