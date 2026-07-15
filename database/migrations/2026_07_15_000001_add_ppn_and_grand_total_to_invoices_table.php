<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('ppn', 15, 2)->default(0)->after('total_nilai');
            $table->decimal('grand_total', 15, 2)->default(0)->after('ppn');
        });

        DB::table('invoices')->orderBy('id')->eachById(function (object $invoice): void {
            $ppn = round((float) $invoice->total_nilai * 0.11);

            DB::table('invoices')->where('id', $invoice->id)->update([
                'ppn' => $ppn,
                'grand_total' => (float) $invoice->total_nilai + $ppn,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['ppn', 'grand_total']);
        });
    }
};
