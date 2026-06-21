<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permintaan_dana', function (Blueprint $table): void {
            if (Schema::hasIndex('permintaan_dana', 'permintaan_dana_status_tgl_pd_index')) {
                $table->dropIndex('permintaan_dana_status_tgl_pd_index');
            }

            foreach (['tgl_pd', 'nominal'] as $column) {
                if (Schema::hasColumn('permintaan_dana', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('permintaan_dana', function (Blueprint $table): void {
            if (! Schema::hasColumn('permintaan_dana', 'tgl_pd')) {
                $table->date('tgl_pd')->nullable();
            }

            if (! Schema::hasColumn('permintaan_dana', 'nominal')) {
                $table->decimal('nominal', 15, 2)->nullable();
            }
        });
    }
};
