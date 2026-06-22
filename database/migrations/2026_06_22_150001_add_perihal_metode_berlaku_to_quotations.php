<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            if (! Schema::hasColumn('quotations', 'perihal')) {
                $table->string('perihal', 200)->nullable()->after('catatan');
            }

            if (! Schema::hasColumn('quotations', 'metode_pembayaran')) {
                $table->string('metode_pembayaran', 100)->nullable()->after('perihal');
            }

            if (! Schema::hasColumn('quotations', 'masa_berlaku')) {
                $table->date('masa_berlaku')->nullable()->after('metode_pembayaran');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            if (Schema::hasColumn('quotations', 'masa_berlaku')) {
                $table->dropColumn('masa_berlaku');
            }

            if (Schema::hasColumn('quotations', 'metode_pembayaran')) {
                $table->dropColumn('metode_pembayaran');
            }

            if (Schema::hasColumn('quotations', 'perihal')) {
                $table->dropColumn('perihal');
            }
        });
    }
};
