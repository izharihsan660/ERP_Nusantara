<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wip_orders') && Schema::hasColumn('wip_orders', 'purchase_order_id')) {
            Schema::table('wip_orders', function (Blueprint $table) {
                $table->dropForeign(['purchase_order_id']);
                $table->dropIndex(['purchase_order_id', 'status']);
            });
        }

        if (Schema::hasTable('purchase_orders') && ! Schema::hasTable('sales_orders')) {
            Schema::rename('purchase_orders', 'sales_orders');
        }

        if (Schema::hasTable('wip_orders') && Schema::hasColumn('wip_orders', 'purchase_order_id')) {
            Schema::table('wip_orders', function (Blueprint $table) {
                $table->renameColumn('purchase_order_id', 'sales_order_id');
            });
        }

        if (Schema::hasTable('wip_orders') && Schema::hasColumn('wip_orders', 'sales_order_id')) {
            Schema::table('wip_orders', function (Blueprint $table) {
                $table->foreign('sales_order_id')->references('id')->on('sales_orders')->cascadeOnUpdate()->restrictOnDelete();
                $table->index(['sales_order_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wip_orders') && Schema::hasColumn('wip_orders', 'sales_order_id')) {
            Schema::table('wip_orders', function (Blueprint $table) {
                $table->dropForeign(['sales_order_id']);
                $table->dropIndex(['sales_order_id', 'status']);
            });
        }

        if (Schema::hasTable('wip_orders') && Schema::hasColumn('wip_orders', 'sales_order_id')) {
            Schema::table('wip_orders', function (Blueprint $table) {
                $table->renameColumn('sales_order_id', 'purchase_order_id');
            });
        }

        if (Schema::hasTable('sales_orders') && ! Schema::hasTable('purchase_orders')) {
            Schema::rename('sales_orders', 'purchase_orders');
        }

        if (Schema::hasTable('wip_orders') && Schema::hasColumn('wip_orders', 'purchase_order_id')) {
            Schema::table('wip_orders', function (Blueprint $table) {
                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnUpdate()->restrictOnDelete();
                $table->index(['purchase_order_id', 'status']);
            });
        }
    }
};
