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
        // ✅ BUAT TABEL BATCH PRODUK
        Schema::create('tbl_batch_produks', function (Blueprint $table) {
            $table->increments('batch_id');
            $table->unsignedInteger('produk_id');
            $table->string('barcode_batch', 100)->unique()->comment('Barcode unik per batch');
            $table->integer('stok')->default(0)->comment('Stok per batch ini');
            $table->date('kadaluwarsa')->comment('Tanggal kadaluwarsa batch ini');
            $table->decimal('harga_beli', 12, 2)->comment('Harga beli batch ini');
            $table->unsignedInteger('pembelian_id')->nullable()->comment('Referensi ke pembelian');
            $table->timestamps();

            // Foreign keys
            $table->foreign('produk_id')->references('produk_id')->on('tbl_produks')->onDelete('cascade');
            $table->foreign('pembelian_id')->references('pembelian_id')->on('tbl_pembelians')->onDelete('set null');

            // Indexes untuk query cepat
            $table->index('produk_id');
            $table->index('kadaluwarsa');
            $table->index('barcode_batch');
        });

        // ✅ UPDATE TABEL DETAIL PEMBELIANS - TAMBAH BATCH_ID
        Schema::table('tbl_detail_pembelians', function (Blueprint $table) {
            // Tambah kolom batch_id untuk referensi
            if (!Schema::hasColumn('tbl_detail_pembelians', 'batch_id')) {
                $table->unsignedInteger('batch_id')->nullable()->after('produk_id');
                $table->foreign('batch_id')->references('batch_id')->on('tbl_batch_produks')->onDelete('set null');
            }

            // CATATAN: kadaluwarsa dan barcode_batch sudah ada dari migration sebelumnya
            // Jadi tidak perlu ditambahkan lagi di sini
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key dulu dari detail_pembelians
        Schema::table('tbl_detail_pembelians', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_detail_pembelians', 'batch_id')) {
                $table->dropForeign(['batch_id']);
                $table->dropColumn('batch_id');
            }
        });

        // Baru drop tabel batch_produks
        Schema::dropIfExists('tbl_batch_produks');
    }
};