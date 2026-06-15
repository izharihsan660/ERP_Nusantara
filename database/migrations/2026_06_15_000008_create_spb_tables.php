<?php

use App\Enums\ReferensiTipe;
use App\Enums\SpbStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spb', function (Blueprint $table) {
            $table->id();
            $table->string('no_spb', 50)->unique();
            $table->date('tgl_spb');
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('template_id')->constrained('document_templates')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('spb_able_type', 100);
            $table->unsignedBigInteger('spb_able_id');
            $table->enum('referensi_tipe', array_map(fn (ReferensiTipe $tipe): string => $tipe->value, ReferensiTipe::cases()));
            $table->string('no_referensi', 50);
            $table->string('nama_ekspedisi', 100);
            $table->date('etd')->nullable();
            $table->date('eta')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', array_map(fn (SpbStatus $status): string => $status->value, SpbStatus::cases()))->default(SpbStatus::Draft->value);
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('alasan_void')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->index(['spb_able_type', 'spb_able_id']);
            $table->index(['customer_id', 'tgl_spb']);
            $table->index('status');
        });

        Schema::create('spb_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spb_id')->constrained('spb')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('part_no', 50);
            $table->string('deskripsi', 200);
            $table->unsignedInteger('qty');
            $table->string('satuan', 20);
            $table->decimal('berat', 10, 2);
            $table->decimal('volume', 10, 2);
            $table->string('dimensi', 100)->nullable();
            $table->string('sku', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spb_items');
        Schema::dropIfExists('spb');
    }
};
