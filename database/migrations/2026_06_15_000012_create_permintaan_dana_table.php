<?php

use App\Enums\KategoriPD;
use App\Enums\PDStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_dana', function (Blueprint $table) {
            $table->id();
            $table->string('no_pd', 30)->unique();
            $table->date('tgl_pd');
            $table->enum('kategori', array_map(fn (KategoriPD $kategori): string => $kategori->value, KategoriPD::cases()));
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan');
            $table->string('referensi_dokumen', 100)->nullable();
            $table->enum('status', array_map(fn (PDStatus $status): string => $status->value, PDStatus::cases()))->default(PDStatus::Draft->value);
            $table->timestamp('submitted_at')->nullable();
            $table->string('qr_token', 100)->unique()->nullable();
            $table->text('catatan_rejection')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->date('tgl_realisasi')->nullable();
            $table->decimal('jumlah_realisasi', 15, 2)->nullable();
            $table->string('file_bukti', 255)->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('alasan_void')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->index(['status', 'tgl_pd']);
            $table->index('kategori');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_dana');
    }
};
