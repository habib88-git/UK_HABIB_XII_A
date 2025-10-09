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
        Schema::create('tbl_detail_pembelians', function (Blueprint $table) {
            $table->increments('detail_pembelian_id');
            $table->unsignedInteger('pembelian_id');
            $table->unsignedInteger('produk_id');
            $table->integer('jumlah');
            $table->decimal('harga_beli', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->foreign('pembelian_id')->references('pembelian_id')->on('tbl_pembelians')->onDelete('cascade');
            $table->foreign('produk_id')->references('produk_id')->on('tbl_produks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_detail_pembelians');
    }
};
