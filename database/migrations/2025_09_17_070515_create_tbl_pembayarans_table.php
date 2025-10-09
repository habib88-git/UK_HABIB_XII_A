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
        Schema::create('tbl_pembayarans', function (Blueprint $table) {
            $table->increments('pembayaran_id');
            $table->unsignedInteger('penjualan_id');
            $table->enum('metode', ['cash', 'debit', 'ewallet']);
            $table->decimal('jumlah', 12, 2);
            $table->decimal('kembalian', 10,2)->default(0);
            $table->dateTime('tanggal_pembayaran');
            $table->timestamps();

            $table->foreign('penjualan_id')->references('penjualan_id')->on('tbl_penjualans')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pembayarans');
    }
};
