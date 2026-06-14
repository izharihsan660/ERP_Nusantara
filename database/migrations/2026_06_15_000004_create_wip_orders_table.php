<?php

use App\Enums\StatusSupply;
use App\Enums\TipeOrder;
use App\Enums\WIPStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wip_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('no_wip', 30);
            $table->enum('tipe_order', array_map(fn (TipeOrder $tipe): string => $tipe->value, TipeOrder::cases()));
            $table->string('nama_ekspedisi', 100)->nullable();
            $table->enum('status_supply', array_map(fn (StatusSupply $status): string => $status->value, StatusSupply::cases()))->default(StatusSupply::BelumTersupply->value);
            $table->timestamp('tersupply_at')->nullable();
            $table->enum('status', array_map(fn (WIPStatus $status): string => $status->value, WIPStatus::cases()))->default(WIPStatus::Active->value);
            $table->text('alasan_void')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->index(['purchase_order_id', 'status']);
            $table->index('status_supply');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wip_orders');
    }
};
