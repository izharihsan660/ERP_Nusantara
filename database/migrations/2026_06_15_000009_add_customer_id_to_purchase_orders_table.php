<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('purchase_orders', 'customer_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('tgl_po');
            });
        }

        if (DB::table('purchase_orders')->whereNull('customer_id')->exists()) {
            $defaultCustomerId = DB::table('customers')->orderBy('id')->value('id');

            if (! $defaultCustomerId) {
                throw new RuntimeException('Tidak bisa mengisi customer_id untuk Purchase Order existing karena belum ada data customer.');
            }

            DB::table('purchase_orders')
                ->whereNull('customer_id')
                ->update(['customer_id' => $defaultCustomerId]);
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            $table->foreign('customer_id', 'vendor_purchase_orders_customer_id_foreign')->references('id')->on('customers')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase_orders', 'customer_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropForeign('vendor_purchase_orders_customer_id_foreign');
                $table->dropColumn('customer_id');
            });
        }
    }
};
