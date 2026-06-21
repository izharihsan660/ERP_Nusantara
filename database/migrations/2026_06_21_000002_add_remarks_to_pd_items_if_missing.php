<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pd_items') && ! Schema::hasColumn('pd_items', 'remarks')) {
            Schema::table('pd_items', function (Blueprint $table): void {
                $table->text('remarks')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pd_items') && Schema::hasColumn('pd_items', 'remarks')) {
            Schema::table('pd_items', function (Blueprint $table): void {
                $table->dropColumn('remarks');
            });
        }
    }
};
