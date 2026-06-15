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
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('no_pr_customer', 50)->nullable();
            $table->string('no_po_customer', 50)->nullable();
            $table->enum('status', array_map(fn (PurchaseOrderStatus $status): string => $status->value, PurchaseOrderStatus::cases()))->default(PurchaseOrderStatus::Draft->value);
            $table->string('qr_token', 100)->unique()->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('alasan_void')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->index(['status', 'tgl_po']);
            $table->index('vendor_id');
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('katalog_id')->nullable()->constrained('katalog')->nullOnDelete();
            $table->string('deskripsi', 200);
            $table->unsignedInteger('qty');
            $table->string('satuan', 20);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('jumlah', 15, 2);
            $table->timestamps();

            $table->index('katalog_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
