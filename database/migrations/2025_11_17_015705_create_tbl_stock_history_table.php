<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_stock_history', function (Blueprint $table) {
            $table->increments('stock_history_id');
            $table->unsignedInteger('produk_id');
            $table->enum('tipe', ['masuk', 'keluar'])->comment('masuk = pembelian, keluar = penjualan');
            $table->integer('jumlah')->comment('Jumlah perubahan stok');
            $table->integer('stok_sebelum')->comment('Stok sebelum transaksi');
            $table->integer('stok_sesudah')->comment('Stok setelah transaksi');
            $table->string('keterangan', 255)->nullable()->comment('Detail transaksi');
            $table->string('referensi_tipe', 50)->nullable()->comment('pembelian/penjualan');
            $table->unsignedInteger('referensi_id')->nullable()->comment('ID pembelian/penjualan');
            $table->unsignedInteger('user_id')->nullable()->comment('User yang melakukan transaksi');
            $table->timestamps();

            // Foreign keys
            $table->foreign('produk_id')->references('produk_id')->on('tbl_produks')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('tbl_users')->onDelete('set null');

            // Index untuk query cepat
            $table->index('produk_id');
            $table->index('tipe');
            $table->index(['referensi_tipe', 'referensi_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_stock_history');
    }
};