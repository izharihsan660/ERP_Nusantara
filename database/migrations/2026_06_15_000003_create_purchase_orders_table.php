<?php

use App\Enums\MetodePembayaran;
use App\Enums\POStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->unique()->constrained('quotations')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('no_po_customer', 50);
            $table->string('no_pr_customer', 50)->nullable();
            $table->date('tgl_po');
            $table->enum('metode_pembayaran', array_map(fn (MetodePembayaran $metode): string => $metode->value, MetodePembayaran::cases()));
            $table->unsignedInteger('top_hari')->nullable();
            $table->enum('status', array_map(fn (POStatus $status): string => $status->value, POStatus::cases()))->default(POStatus::Open->value);
            $table->text('alasan_void')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index('tgl_po');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
