<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales_order_documents')) {
            Schema::create('sales_order_documents', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('file_path', 255);
                $table->string('nama_file', 100);
                $table->timestamp('created_at')->nullable();

                $table->index('sales_order_id');
            });
        }

        if (! Schema::hasTable('wip_items')) {
            Schema::create('wip_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('wip_order_id')->constrained('wip_orders')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('katalog_id')->nullable()->constrained('katalog')->nullOnDelete();
                $table->string('part_no', 50);
                $table->string('deskripsi', 200);
                $table->integer('qty');
                $table->timestamp('created_at')->nullable();

                $table->index(['wip_order_id', 'part_no']);
            });
        }

        Schema::table('permintaan_dana', function (Blueprint $table): void {
            if (Schema::hasColumn('permintaan_dana', 'kategori')) {
                if (Schema::hasIndex('permintaan_dana', 'permintaan_dana_kategori_index')) {
                    $table->dropIndex('permintaan_dana_kategori_index');
                }

                $table->dropColumn('kategori');
            }

            if (! Schema::hasColumn('permintaan_dana', 'tujuan')) {
                $table->string('tujuan', 150)->after('tgl_pd');
            }

            if (! Schema::hasColumn('permintaan_dana', 'rekening_tujuan')) {
                $table->string('rekening_tujuan', 200)->after('tujuan');
            }

            if (! Schema::hasColumn('permintaan_dana', 'bank_tujuan')) {
                $table->string('bank_tujuan', 100)->nullable()->after('rekening_tujuan');
            }

            if (! Schema::hasColumn('permintaan_dana', 'plan_pembayaran')) {
                $table->date('plan_pembayaran')->after('bank_tujuan');
            }
        });

        if (! Schema::hasTable('pd_items')) {
            Schema::create('pd_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('permintaan_dana_id')->constrained('permintaan_dana')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('no_po', 50)->nullable();
                $table->string('no_part', 50)->nullable();
                $table->string('description', 200);
                $table->integer('qty');
                $table->decimal('harga', 15, 2);
                $table->decimal('total', 15, 2);
                $table->text('remarks')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index('permintaan_dana_id');
            });
        }

        if (Schema::hasTable('pd_items') && ! Schema::hasColumn('pd_items', 'remarks')) {
            Schema::table('pd_items', function (Blueprint $table): void {
                $table->text('remarks')->nullable()->after('total');
            });
        }

        Schema::table('pd_documents', function (Blueprint $table): void {
            if (! Schema::hasColumn('pd_documents', 'tipe')) {
                $table->enum('tipe', ['NOTA', 'FOTO_BARANG'])->nullable()->after('permintaan_dana_id');
            }
        });

        if (! Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->string('label', 200);
                $table->string('group', 50);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');

        Schema::table('pd_documents', function (Blueprint $table): void {
            if (Schema::hasColumn('pd_documents', 'tipe')) {
                $table->dropColumn('tipe');
            }
        });

        Schema::dropIfExists('pd_items');

        Schema::table('permintaan_dana', function (Blueprint $table): void {
            foreach (['tujuan', 'rekening_tujuan', 'bank_tujuan', 'plan_pembayaran'] as $column) {
                if (Schema::hasColumn('permintaan_dana', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('wip_items');
        Schema::dropIfExists('sales_order_documents');
    }
};
