<?php

use App\Enums\QuotationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('no_quotation', 50)->unique();
            $table->date('tgl_quotation');
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('template_id')->constrained('document_templates')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger('revisi')->default(0);
            $table->enum('status', array_map(fn (QuotationStatus $status): string => $status->value, QuotationStatus::cases()))->default(QuotationStatus::Draft->value);
            $table->text('catatan_rejection')->nullable();
            $table->string('qr_token', 100)->unique()->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('alasan_void')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->index(['status', 'tgl_quotation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
