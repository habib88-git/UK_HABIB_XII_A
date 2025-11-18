<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tbl_detail_penjualans', function (Blueprint $table) {
            // ✅ Tambah kolom batch_id untuk tracking batch mana yang terjual
            if (!Schema::hasColumn('tbl_detail_penjualans', 'batch_id')) {
                $table->unsignedInteger('batch_id')->nullable()->after('produk_id');
                $table->foreign('batch_id')
                    ->references('batch_id')
                    ->on('tbl_batch_produks')
                    ->onDelete('set null');
            }

            // ✅ Tambah kolom untuk menyimpan info batch saat dijual
            if (!Schema::hasColumn('tbl_detail_penjualans', 'barcode_batch')) {
                $table->string('barcode_batch', 100)->nullable()->after('batch_id')
                    ->comment('Barcode batch yang terjual');
            }

            if (!Schema::hasColumn('tbl_detail_penjualans', 'kadaluwarsa_batch')) {
                $table->date('kadaluwarsa_batch')->nullable()->after('barcode_batch')
                    ->comment('Kadaluwarsa batch yang terjual');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_detail_penjualans', function (Blueprint $table) {
            // Drop foreign key dan kolom
            if (Schema::hasColumn('tbl_detail_penjualans', 'batch_id')) {
                $table->dropForeign(['batch_id']);
                $table->dropColumn(['batch_id', 'barcode_batch', 'kadaluwarsa_batch']);
            }
        });
    }
};