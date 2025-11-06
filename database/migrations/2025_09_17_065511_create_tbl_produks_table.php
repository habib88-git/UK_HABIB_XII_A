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
        Schema::create('tbl_produks', function (Blueprint $table) {
            $table->increments('produk_id');
            $table->string('barcode', 50)->unique();
            $table->string('nama_produk', 100);
            $table->string('photo')->nullable();
            $table->decimal('harga_jual', 10, 2);
            $table->decimal('harga_beli', 10, 2);
            $table->integer('stok')->default(0);
            $table->date('kadaluwarsa');
            $table->unsignedInteger('kategori_id');
            $table->unsignedInteger('satuan_id');
            $table->timestamps();

            $table->foreign('kategori_id')->references('kategori_id')->on('tbl_kategoris')->onDelete('cascade');
            $table->foreign('satuan_id')->references('satuan_id')->on('tbl_satuans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_produks');
    }
};
