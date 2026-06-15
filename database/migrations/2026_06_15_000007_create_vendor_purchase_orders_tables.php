<?php

use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('no_purchase_order', 50)->unique();
            $table->date('tgl_po');
            $table->unsignedBigInteger('vendor_id');
            $table->string('no_pr_customer', 50)->nullable();
            $table->string('no_po_customer', 50)->nullable();
            $table->enum('status', array_map(fn (PurchaseOrderStatus $status): string => $status->value, PurchaseOrderStatus::cases()))->default(PurchaseOrderStatus::Draft->value);
            $table->string('qr_token', 100)->unique()->nullable();
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->text('alasan_void')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index(['status', 'tgl_po']);
            $table->index('vendor_id');
            $table->foreign('vendor_id', 'vendor_purchase_orders_vendor_id_foreign')->references('id')->on('vendors')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('approved_by', 'vendor_purchase_orders_approved_by_foreign')->references('id')->on('users')->nullOnDelete();
            $table->foreign('voided_by', 'vendor_purchase_orders_voided_by_foreign')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by', 'vendor_purchase_orders_created_by_foreign')->references('id')->on('users')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('katalog_id')->nullable();
            $table->string('deskripsi', 200);
            $table->unsignedInteger('qty');
            $table->string('satuan', 20);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('jumlah', 15, 2);
            $table->timestamps();

            $table->index('katalog_id');
            $table->foreign('purchase_order_id', 'vendor_po_items_purchase_order_id_foreign')->references('id')->on('purchase_orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('katalog_id', 'vendor_po_items_katalog_id_foreign')->references('id')->on('katalog')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
