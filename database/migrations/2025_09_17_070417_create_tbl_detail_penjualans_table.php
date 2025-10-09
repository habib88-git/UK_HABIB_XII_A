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
        Schema::create('tbl_detail_penjualans', function (Blueprint $table) {
            $table->increments('detail_id');
            $table->unsignedInteger('penjualan_id');
            $table->unsignedInteger('produk_id');
            $table->integer('jumlah_produk');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->foreign('penjualan_id')->references('penjualan_id')->on('tbl_penjualans')->onDelete('cascade');
            $table->foreign('produk_id')->references('produk_id')->on('tbl_produks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_detail_penjualans');
    }
};
