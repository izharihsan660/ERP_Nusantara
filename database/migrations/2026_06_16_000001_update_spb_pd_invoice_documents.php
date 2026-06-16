<?php

use App\Enums\InvoicePaymentDocumentType;
use App\Enums\PdDocumentKategori;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spb_items', function (Blueprint $table): void {
            if (Schema::hasColumn('spb_items', 'satuan')) {
                $table->dropColumn('satuan');
            }
        });

        Schema::table('permintaan_dana', function (Blueprint $table): void {
            if (Schema::hasColumn('permintaan_dana', 'file_bukti')) {
                $table->dropColumn('file_bukti');
            }
        });

        Schema::create('pd_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('permintaan_dana_id')->constrained('permintaan_dana')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('kategori', array_map(fn (PdDocumentKategori $kategori): string => $kategori->value, PdDocumentKategori::cases()));
            $table->string('file_path', 255);
            $table->string('nama_file', 100);
            $table->timestamp('created_at')->nullable();

            $table->index(['permintaan_dana_id', 'kategori']);
        });

        Schema::create('invoice_payment_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('tipe_dokumen', array_map(fn (InvoicePaymentDocumentType $tipe): string => $tipe->value, InvoicePaymentDocumentType::cases()));
            $table->string('file_path', 255);
            $table->string('nama_file', 100);
            $table->timestamp('created_at')->nullable();

            $table->index(['invoice_id', 'tipe_dokumen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payment_documents');
        Schema::dropIfExists('pd_documents');

        Schema::table('permintaan_dana', function (Blueprint $table): void {
            if (! Schema::hasColumn('permintaan_dana', 'file_bukti')) {
                $table->string('file_bukti', 255)->nullable()->after('jumlah_realisasi');
            }
        });

        Schema::table('spb_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('spb_items', 'satuan')) {
                $table->string('satuan', 20)->nullable()->after('qty');
            }
        });
    }
};
