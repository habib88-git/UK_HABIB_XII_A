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
        // Tambah kolom kadaluwarsa dan barcode_batch di tabel detail pembelians
        Schema::table('tbl_detail_pembelians', function (Blueprint $table) {
            $table->date('kadaluwarsa')->nullable()->after('subtotal')->comment('Tanggal kadaluwarsa per batch pembelian');
            $table->string('barcode_batch', 100)->nullable()->after('kadaluwarsa')->comment('Barcode produk saat pembelian ini');

            // Index untuk query cepat berdasarkan kadaluwarsa
            $table->index('kadaluwarsa', 'idx_kadaluwarsa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_detail_pembelians', function (Blueprint $table) {
            $table->dropIndex('idx_kadaluwarsa');
            $table->dropColumn(['kadaluwarsa', 'barcode_batch']);
        });
    }
};
