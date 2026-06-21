<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('permintaan_dana', 'kategori')) {
            Schema::table('permintaan_dana', function (Blueprint $table): void {
                if (Schema::hasIndex('permintaan_dana', 'permintaan_dana_kategori_index')) {
                    $table->dropIndex('permintaan_dana_kategori_index');
                }
            });
        }
    }

    public function down(): void
    {
        // no op
    }
};
